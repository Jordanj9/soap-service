<?php

namespace App\Http\Controllers;

use App\Service\SoapService;
use Illuminate\Http\Request;
use SoapServer;

class SoapController extends Controller
{
   public function handleSoapRequest(Request $request): void
   {
       $server = new SoapServer(null,[
           'uri' => url('/soap')
       ]);
       $server->setClass(SoapService::class);
       $server->handle();
   }
}
