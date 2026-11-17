<?php

test('filament login screen renders successfully', function () {
    $response = $this->get('/dashboard/login');

    $response->assertStatus(200);
});
