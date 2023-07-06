<?php

use Phinx\Migration\AbstractMigration;

class CreateSocialAuthTable extends AbstractMigration
{
    public function change()
    {
        $this->table('socialauth_providers')
            ->addColumn("name", "string")
            ->addColumn("enabled", "boolean", ['default' => true])
            ->addColumn("client_id", "text")
            ->addColumn("client_secret", "text")
            ->addColumn("redirect_uri", "text")
            ->create();

        $this->table('socialauth_users')
            ->addColumn("provider_id", "string")
            ->addColumn("user_id", "integer")
            ->addColumn("provider", "string")
            ->addColumn("refresh_token", "string")

            ->create();
    }
}
