<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\LoginNeedsVerification;
use Illuminate\Http\Request;
use Twilio\Rest\Client;


class LoginController extends Controller
{
    //
    public function submit(Request $request)
    {
        //perform login

        //validate phone number
        $request->validate([
            'phone' => 'required|string|min:10|regex:/^\+?[0-9]{10,}$/',
        ]);

        //find or create user model
        $user = User::firstOrCreate([
            'phone' => $request->phone
        ]);

        if (!$user) {
            return response()->json(['message' => 'Could not process a user with that phone number'], 401);
        }

        //send the user a one time use code
        $user->notify(new LoginNeedsVerification());

        //return back a response
        return response()->json(['message' => 'Text message notification sent.']);
    }
//    public function verify(Request $request)
//    {
//        // Valideer de binnenkomende aanvraag
//        $request->validate([
//            'phone' => 'required|string|min:10|regex:/^\+?[0-9]{10,}$/',
//            'login_code' => 'required|numeric|between:111111,999999',
//        ]);
//
//        // Maak een instantie van de Twilio client
//        $twilio = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));
//
//        try {
//            // Gebruik de Verify service om de code te verifiëren
//            $verification_check = $twilio->verify->v2->services(env('TWILIO_VERIFY_SID'))
//                ->verificationChecks
//                ->create([
//                    "to" => $request->phone,
//                    "code" => $request->login_code,
//                ]);
//
//            // Controleer of de verificatie succesvol was
//            if ($verification_check->status === "approved") {
//                $user = User::where('phone', $request->phone)->firstOrFail();
//
//                // Verwijder de login_code of andere acties indien nodig
//                // Bijvoorbeeld, het updaten van de gebruikerstatus of het creëren van een token
//
//                return response()->json(['message' => 'Verification successful.']);
//            }
//        } catch (\Exception $e) {
//            // Handel fouten af, bijvoorbeeld door te loggen of een foutbericht terug te sturen
//        }
//
//        return response()->json(['message' => 'Invalid verification code.'], 401);
//    }

//    public function verify(Request $request)
//    {
//        //validate incoming request
//        $request->validate([
//            'phone' => 'required|string|min:10|regex:/^\+?[0-9]{10,}$/',
//            'login_code' => 'required|numeric|between:111111,999999'
//        ]);
//        //find the user
//        $user = User::where('phone', $request->phone)
//            ->where('login_code', $request->login_code)
//            ->first();
//        //is the code provided the same one saved?
//        //if so, return back an auth token
//        if($user) {
//            $user->update([
//                'login_code' => null
//            ]);
//            return $user->createToken($request->login_code)->plainTextToken;
//        }
//        //if not, return back a message
//        return response()->json(['message' => 'Invalid verification code.'], 401);
//    }

    public function verify(Request $request)
    {
        // Valideer de binnenkomende aanvraag
        $request->validate([
            'phone' => 'required|string|min:10|regex:/^\+?[0-9]{10,}$/',
            'login_code' => 'required|numeric|between:000000,999999',
        ]);

        // Maak een instantie van de Twilio client
        $twilio = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));

        try {
            // Gebruik Twilio's Verify service om de code te verifiëren
            $verification_check = $twilio->verify->v2->services(env('TWILIO_VERIFY_SID'))
                ->verificationChecks
                ->create([
                    'to' => $request->phone,
                    'code' => $request->login_code,
                ]);

            // Controleer of de verificatie succesvol was
            if ($verification_check->status === "approved") {
                // Zoek de gebruiker op basis van telefoonnummer
                $user = User::where('phone', $request->phone)->firstOrFail();

                // Omdat de verificatie succesvol is, verwijderen we de login_code (indien eerder gebruikt) en genereren we een token
                $user->login_code = null; // Zorg ervoor dat deze lijn relevant is voor je applicatie
                $user->save();

                // Genereer een nieuw token voor de gebruiker
                $token = $user->createToken('Personal Access Token')->plainTextToken;

                // Retourneer het token en een succesbericht
                return response()->json(['message' => 'Verification successful.', 'token' => $token]);
            } else {
                // Als de status niet "approved" is, is de code ongeldig
                return response()->json(['message' => 'Invalid verification code.'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Verification failed.', 'error' => $e->getMessage()], 401);
        }

    }
}
