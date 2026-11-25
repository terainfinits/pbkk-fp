<?php
// Fitur: Halaman IDE dan navigasi
// connect with ../../routes/web.php

test('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
