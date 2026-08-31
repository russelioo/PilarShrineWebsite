<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginRouteTest extends TestCase
{
    public function test_the_login_page_redirects_to_the_vue_login_portal(): void
    {
        $this->get('/login')->assertRedirect('/#/login');
    }

    public function test_an_unauthenticated_livestream_update_redirects_to_login(): void
    {
        $this->post('/admin/livestream')->assertRedirect('/login');
    }
}
