<?php

test('the homepage redirects guests to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
