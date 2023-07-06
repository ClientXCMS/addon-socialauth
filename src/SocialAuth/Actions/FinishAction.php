<?php

namespace App\SocialAuth\Actions;

use App\Account\Services\SignupService;
use App\Auth\Database\SecurityQuestionTable;
use App\SocialAuth\SocialAuthService;
use ClientX\Actions\Action;
use ClientX\Renderer\RendererInterface;
use ClientX\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;

class FinishAction extends Action
{
    private SessionInterface $session;
    private SocialAuthService $service;
    private SecurityQuestionTable $securityQuestionTable;
    private array $options;
    private SignupService $signupService;

    public function __construct(SessionInterface $session, SocialAuthService $service, RendererInterface $renderer, string $options,SecurityQuestionTable $securityQuestionTable)
    {
        $this->session = $session;
        $this->service = $service;
        $this->renderer = $renderer;
        $this->securityQuestionTable = $securityQuestionTable;

        $this->options = explode(',', $options);
        if (!empty($this->options)) {
            $this->options[] = 'Other';
        }
        $this->options = collect($this->options)->mapWithKeys(function ($option) {
            return  [$option  => $option];
        })->toArray();
    }

    public function __invoke(ServerRequestInterface $request)
    {

        $username = $this->session->get('socialauth.username');
        $email = $this->session->get('socialauth.email');
        $errors = [];
        if ($request->getMethod() == 'POST'){

            $params = $request->getParsedBody();
            $validator = $this->service->validate($params);
            if ($validator->isValid()) {
                return $this->service->finish($params);
            } else {
                $errors = $validator->getErrors();
            }
        }
        $options = $this->options;
        $questions = $this->getQuestions();
        return $this->render('@auth/socialauth', compact('username', 'email', 'questions', 'options', 'errors'));
    }


    private function getQuestions()
    {
        return collect($this->securityQuestionTable->findAll())->mapWithKeys(function ($question) {
            return [$question->id => $question->question];
        })->toArray();
    }
}