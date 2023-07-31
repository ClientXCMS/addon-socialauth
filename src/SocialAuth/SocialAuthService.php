<?php

namespace App\SocialAuth;

use App\Account\User;
use App\Auth\Database\UserTable;
use App\Auth\DatabaseUserAuth;
use App\SocialAuth\Database\ProviderTable;
use App\SocialAuth\Database\SocialAuthUserTable;
use App\SocialAuth\Entity\ProviderEntity;
use App\SocialAuth\Event\SocialAuthLoginEvent;
use App\SocialAuth\Event\SocialAuthSignupEvent;
use App\SocialAuth\Providers\SocialAuthProviderInterface;
use App\Translation\TranslationTrait;
use ClientX\Actions\Traits\FlashTrait;
use ClientX\Crypt\Crypter;
use ClientX\Database\Hydrator;
use ClientX\Database\NoRecordException;
use ClientX\Event\EventManager;
use ClientX\Helpers\AccountStatus;
use ClientX\Helpers\Passwords;
use ClientX\Helpers\Str;
use ClientX\Response\RedirectResponse;
use ClientX\Router;
use ClientX\Session\FlashService;
use ClientX\Session\SessionInterface;

use App\Account\Event\SignupEvent;
use App\Auth\Event\LoginEvent;
use ClientX\Translator\Translater;
use ClientX\Validator;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;

class SocialAuthService
{
    private array $providers = [];
    private ProviderTable $providerTable;
    private UserTable $userTable;
    private SocialAuthUserTable $authUserTable;
    private DatabaseUserAuth $user;
    private Translater $translater;
    private SessionInterface $session;
    /**
     * @var string[]
     */
    private array $banned;
    private bool $tosCheck;
    private string $locale;
    private Router $router;
    private EventManager $event;

    use TranslationTrait;
    use FlashTrait;

    public function __construct(
        array               $providers,
        DatabaseUserAuth    $user,
        UserTable           $userTable,
        SocialAuthUserTable $authUserTable,
        ProviderTable       $providerTable,
        Translater          $translater,
        SessionInterface    $session,
        EventManager $event,
        Router $router,
        string $banned,
        string $tosLinks,
        string $locale,
    )
    {
        $this->providers = $providers;
        $this->providerTable = $providerTable;
        $this->userTable = $userTable;
        $this->authUserTable = $authUserTable;
        $this->user = $user;
        $this->flash = new FlashService($session);
        $this->translater = $translater;
        $this->session = $session;
        $this->event = $event;
        $this->banned = explode(',', $banned);
        $this->tosCheck = empty($tosLinks) == false;
        $this->locale = $locale;
        $this->router = $router;
    }

    /**
     * @param string $name
     * @return AbstractProvider|null|SocialAuthProviderInterface
     */
    public function getProvider(string $name, $withconfig = true):?AbstractProvider
    {
        /** @var AbstractProvider $provider */
        $provider = collect($this->providers)->filter(function(SocialAuthProviderInterface $social) use ($name){
            return $social->name() == $name;
        })->first();
        if ($provider == null){
            throw new \Exception('Cannot find provider ' . $provider->name());
        }
        if ($withconfig){

            try {
                /** @var ProviderEntity $config */
                $config = $this->providerTable->findBy("name", $provider->name());
                if (!$config->isEnabled()){
                    throw new \Exception('Config is not enabled for ' . $provider->name());
                }
                return new $provider($config->toArray());
            } catch (NoRecordException $e){
                throw new \Exception('Cannot find config for ' . $provider->name());
            }
        }
        return new $provider();
    }

    public function findEnabled(bool $force = true):array
    {
        $configs = $this->providerTable->makeQuery()->select('name')->where('enabled = 1')->fetchAll();
        if ($force){

            return collect($configs)->map(function(ProviderEntity $entity) {
                return $this->getProvider($entity->getName());
            })->toArray();
        }
        return collect($configs->toArray())->map(function(ProviderEntity $entity){
            return $entity->getName();
        })->toArray();
    }
    public function authorize(ResourceOwnerInterface $owner, AccessToken $token, $provider)
    {
        try {
            /** @var User $user */
            $user = $this->userTable->findBy("email", $owner->getEmail());
            if ($this->authUserTable->isSignupWithSocial($user->getId())){
                $this->user->setUser($user);
                $this->flash->success($this->translater->trans('socialauth.success'));
                $this->event->trigger(new SocialAuthLoginEvent($this->authUserTable->findBy("user_id", $user->getId())));
                $this->event->trigger(new LoginEvent($user));

                return new RedirectResponse('/client');
            } else {
                $this->flash->success($this->translater->trans('socialauth.already'));
                return new RedirectResponse('/auth/login');

            }
        } catch (NoRecordException $e){
            $this->session->set('socialauth.username', $owner->getUsername());
            $this->session->set('socialauth.email', $owner->getEmail());
            $this->session->set('socialauth.id', $owner->getId());
            $this->session->set('socialauth.provider', $provider);
            $this->session->set('socialauth.token', $token->getRefreshToken());
            $this->session->set('socialauth.toarray', $owner->toArray());

            return new RedirectResponse('/socialauth/finish');
        }
    }

    public function validate(array $params):Validator
    {
        $email = $this->session->get('socialauth.email');

        $params['email'] = $email;
        $validator =  (new Validator($params))
            ->notEmpty('question_reply', 'firstname', 'lastname')
            ->notBannedEmail('email', $this->banned)
            ->length("firstname", 1, 100)
            ->length("lastname", 1, 100);

        if ($this->tosCheck) {
            if (array_key_exists('inputAcceptTos', $params) === false) {
                $validator->addError('inputAcceptTos', 'acceptTos');
            }
        }
        return $validator;
    }

    public function signupParams(array $params){

        $email = $this->session->get('socialauth.email');

        $how_did_you_find_us = null;
        if (array_key_exists('how_did_you_find_us', $params)) {
            $how_did_you_find_us = $params['how_did_you_find_us'];
            if ($how_did_you_find_us === 'Other') {
                $how_did_you_find_us = $params['how_did_you_find_us_none'];
            }
        }
        return [
            'firstname' => $params['firstname'],
            'lastname' => $params['lastname'],
            'password' => Passwords::hash(Str::randomStr(128)),
            'status' => AccountStatus::STATUS_ACTIVE,
            'confirmation_token' => null,
            'email' => $email,
            'locale' => $this->locale,
            'howdidyoufindus' => $how_did_you_find_us,
            'securityquestions_id' => (int)$params['question_id'] ?? null,
            'securityquestions_answer' => $params['question_reply'] ?? null,
        ];
    }

    public function finish(array $params)
    {

        $email = $this->session->get('socialauth.email');
        $provider = $this->session->get('socialauth.provider');
        $refresh = $this->session->get('socialauth.token');
        $id = $this->session->get('socialauth.id');
        $params = $this->signupParams($params);
        /** @var \App\Auth\User */
        $user = Hydrator::hydrate($params, \App\Auth\User::class);
        $userId = $this->userTable->insert($params);
        $user->id = $userId;
        $this->user->setUser($userId);
        $this->authUserTable->signup($userId, (int)$id, $provider, $refresh ?? 'null');
        $this->event->trigger(new SocialAuthSignupEvent($this->authUserTable->findBy("user_id", $user->getId())));
        $this->event->trigger(new SignupEvent($user));

        $this->clearSession();
        return (new RedirectResponse($this->router->generateURI('account')));
    }

    public function clearSession()
    {
        $keys = ['socialauth.email', 'socialauth.provider', 'socialauth.username', 'socialauth.id', 'socialauth.toarray', 'socialauth.token'];
        collect($keys)->map(function($key){
            $this->session->delete($key);
        });
    }

    public function findAll():array
    {
         $configs = $this->providerTable->makeQuery()->fetchAll();

        return collect($configs)->map(function(ProviderEntity $entity){
            return $entity->getName();
        })->toArray();
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function saveProvider(string $name, string $clientId, string $secretId, string $redirectUri)
    {
        try {
            $this->providerTable->findBy("name", $name);
            $exist = true;
        } catch (NoRecordException $e){
            $exist = false;
        }
        if ($exist){
            $this->providerTable->update($name, [
                'name' => $name,
                'client_id' => @(new Crypter())->encrypt($clientId),
                'client_secret' => @(new Crypter())->encrypt($secretId),
                'redirect_uri' => $redirectUri
            ], 'name');
        }
        $this->providerTable->insert([
            'name' => $name,
            'client_id' => @(new Crypter())->encrypt($clientId),
            'client_secret' => @(new Crypter())->encrypt($secretId),
            'redirect_uri' => $redirectUri,
            'enabled' => 1
        ]);
    }

    public function findConfig(string $name)
    {

        try {
            /** @var ProviderEntity $config */
            $config = $this->providerTable->findBy("name", $name);
            return $config;
        } catch (NoRecordException $e){
            return null;
        }
    }

    public function switch(string $name, bool $next)
    {
        $this->providerTable->update($name, ['enabled' => (int)$next], 'name');
    }
}