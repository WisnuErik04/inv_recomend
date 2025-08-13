<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('http://inv_recomend.test:8080/admin');
});
