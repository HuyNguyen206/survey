<?php

namespace App\Http\Controllers;

class Error extends Controller
{
	public function index(){
		return view("errors/503");
	}
	
	public function auth(){
		return view("errors/auth");
	}
}
