<?php

namespace App\Service;

use App\Mail\ConfirmacionPagoMail;
use App\Models\Client;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class SoapService
{
    public function registerClient($documento, $nombres, $email, $celular): array
    {
        try {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->response(false, 400, 'El email no es válido');
            }
            if (Client::where('email', $email)->exists()) {
                return $this->response(false, 400, 'El email ya está en uso');
            }

            if (Client::where('documento', $documento)->exists()) {
                return $this->response(false, 400, 'El cliente ya existe');
            }

            $client = Client::create([
                'documento' => $documento,
                'nombres' => $nombres,
                'email' => $email,
                'celular' => $celular,
            ]);
            Wallet::create(['client_id' => $client->id, 'saldo' => 0]);

            return $this->response(true, 00, 'Cliente registrado con éxito');
        } catch (Exception $e) {
            return $this->response(false, $e->getCode(), $e->getMessage());
        }
    }

    public function rechargeWallet($documento, $celular, $valor)
    {
        try {
            $client = Client::where('documento', $documento)->where('celular', $celular)->firstOrFail();
            $wallet = $client->wallet;
            $wallet->saldo += $valor;
            $wallet->save();

            return $this->response(true, 00, 'Billetera recargada con éxito');
        } catch (Exception $e) {
            return $this->response(false, $e->getCode(), $e->getMessage());
        }
    }

    public function pay($documento, $celular, $valor): array
    {
        try {
            $client = Client::where('documento', $documento)->where('celular', $celular)->firstOrFail();
            $wallet = $client->wallet;

            if ($wallet->saldo < $valor) {
                return $this->response(false, 400, 'Saldo insuficiente');
            }

            $token = Str::random(6);
            $sessionId = Str::uuid();
//            Mail::to($client->email)->send(new ConfirmacionPagoMail($token,$valor));

            session()->put('session_id', $sessionId);
            session()->put('token', $token);
            session()->put('client_id', $client->id);
            session()->put('valor', $valor);


            return $this->response(true, 00, 'Token enviado al correo', ['id_sesion' => session()->get('session_id'), 'token' => session()->get('token')]);
        } catch (Exception $e) {
            return $this->response(false, $e->getCode(), $e->getMessage());
        }
    }

    public function confirmPayment($id_sesion, $token): array
    {
        try {
            if (session()->get('token') != $token || session()->get('session_id') != $id_sesion) {
                return $this->response(false, 400, 'Token o sesión inválidos');
            }

            $client = Client::find(session()->get('client_id'));
            $wallet = $client->wallet;

            $valor = session()->get('valor');
            $wallet->saldo -= $valor;
            $wallet->save();

            session()->forget(['token', 'client_id', 'valor', 'session_id']);

            return $this->response(true, 00, 'Pago confirmado');
        } catch (Exception $e) {
            return $this->response(false, $e->getCode(), $e->getMessage());
        }
    }

    public function checkBalance($documento, $celular): array
    {
        try {
            $client = Client::where('documento', $documento)->where('celular', $celular)->firstOrFail();
            return $this->response(true, 00, 'Saldo consultado con éxito', ['saldo' => number_format($client->wallet->saldo)]);
        } catch (Exception $e) {
            return $this->response(false, $e->getCode(), $e->getMessage());
        }
    }

    private function response(bool $success, $cod_error, string $message_error, array $data = null): array
    {
        return [
            'success' => $success,
            'cod_error' => $cod_error,
            'message_error' => $message_error,
            'data' => $data,
        ];
    }
}