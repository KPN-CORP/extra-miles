<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class SsoController extends Controller
{
    public function dbauth(Request $request)
    {
        return $this->handleDbauth($request, route('admin.dashboard', absolute: false), 'kpnem');
    }

    private function handleDbauth(Request $request, $redirectRoute, $sessionValue)
    {
        $encryptedData = $request->data;
        $decodedData = base64_decode($encryptedData);

        $key = '666666';
        $decryptedDataxor = $this->xorDecrypt($decodedData, $key);
        $decryptedData = base64_decode($decryptedDataxor);

        $decryptedDataArray = json_decode($decryptedData, true);
        $email = $decryptedDataArray['email'];
        $token = $decryptedDataArray['token'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://kpncorporation.darwinbox.com/checkToken',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(array(
                "api_key" => "3bbfc6dfa28df2a81bd45192bf4f96b72628ae0ec9921a062aef937b7f25d6c704ccfc9539e70e5939a45cc43f3b7ce61477c7135a83bdbd6f85d5c38b5fc563",
                "token" => $token,
            )),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic S1BOX1NTTzpUTXNfJDU2T3BzJXB3',
                'Cookie: __cf_bm=4uUEj1zmjV.MExppSaO8PotAtVYX3j1LC37K7VZbRrA-1712303016-1.0.1.1-t6I22efQWtYGVIwVMpn7P63eop_5tmi8pU7n_ju6i2_AD1YM846eQF2VlfbZKoC.ZwvzWCyaXDISwvp.JP2TPQ; _cfuvid=kEL.TVWTCuZAsepIdMuvd7X9.q7rTz4SP9.769IZWFQ-1712126032738-0.0.1.1-604800000; session=83c35e478c2cafccac60fced59ff2f30'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $responseData = json_decode($response, true);
        $status = $responseData['status'];

        if($status==1){
            $user = User::where('email', $email)->first();
            if ($user) {
                Auth::login($user);
                $user->token = $token;
                $user->email_log = $email;
                $user->save();
                $request->session()->put('system', $sessionValue);
                $request->session()->regenerate();
                return redirect()->intended($redirectRoute);
            } else {
                Alert::error('Login Failed, Please Contact Administrator')->showConfirmButton('OK');
                if (url()->previous()) {
                    return redirect()->back();
                } else {
                    return redirect('https://kpncorporation.darwinbox.com/');
                }
            }
        } else {
            Alert::error('Login Failed, Please Contact Administrator')->showConfirmButton('OK');
            if (url()->previous()) {
                return redirect()->back();
            } else {
                return redirect('https://kpncorporation.darwinbox.com/');
            }
        }
    }

    public function dbauthlms(Request $request)
    {
        return $this->handleDbauthlms($request, route('admin.dashboard', absolute: false), 'kpnlms');
    }

    private function handleDbauthlms(Request $request, $redirectRoute, $sessionValue)
    {
        $encryptedData = $request->data;
        $decodedData = base64_decode($encryptedData);

        $key = '666666';
        $decryptedDataxor = $this->xorDecrypt($decodedData, $key);
        $decryptedData = base64_decode($decryptedDataxor);

        $decryptedDataArray = json_decode($decryptedData, true);
        $email = $decryptedDataArray['email'];
        $token = $decryptedDataArray['token'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://kpncorporation.darwinbox.com/checkToken',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(array(
                "api_key" => "3bbfc6dfa28df2a81bd45192bf4f96b72628ae0ec9921a062aef937b7f25d6c704ccfc9539e70e5939a45cc43f3b7ce61477c7135a83bdbd6f85d5c38b5fc563",
                "token" => $token,
            )),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic S1BOX1NTTzpUTXNfJDU2T3BzJXB3',
                'Cookie: __cf_bm=4uUEj1zmjV.MExppSaO8PotAtVYX3j1LC37K7VZbRrA-1712303016-1.0.1.1-t6I22efQWtYGVIwVMpn7P63eop_5tmi8pU7n_ju6i2_AD1YM846eQF2VlfbZKoC.ZwvzWCyaXDISwvp.JP2TPQ; _cfuvid=kEL.TVWTCuZAsepIdMuvd7X9.q7rTz4SP9.769IZWFQ-1712126032738-0.0.1.1-604800000; session=83c35e478c2cafccac60fced59ff2f30'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $responseData = json_decode($response, true);
        $status = $responseData['status'];

        if($status==1){
            $user = User::where('email', $email)->first();
            if ($user) {
                //disini akan ada proses pengiriman data $user->email, $user->employee_id dan JWT Token ke link vendor, token ini ada masa aktifnya jadi proses sinkronisasi ini tidak bisa lama. bantu buatkan scriptnya disini dan JWT tanpa firebase
                $email = $user->email;
                $employee_id = $user->employee_id;
                $secretKey = 'KPNLMS2025';

                $payload = [
                    'iss' => 'KPN',                 // issuer
                    'aud' => 'VENDOR_LMS',          // audience
                    'iat' => time(),                // issued at
                    'exp' => time() + 120,           // expired dalam 120 detik
                    'email' => $email,
                    'employee_id' => $employee_id
                ];
                
                function base64UrlEncode($data)
                {
                    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
                }

                $header = base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
                $payloadEncoded = base64UrlEncode(json_encode($payload));
                $signature = base64UrlEncode(hash_hmac('sha256', "$header.$payloadEncoded", $secretKey, true));

                $jwt = "$header.$payloadEncoded.$signature";

                // === STEP 5: Redirect ke vendor dengan token ===
                $vendorURL = "http://localhost/lms/cektoken.php?token=" . urlencode($jwt);
                header("Location: $vendorURL");
                exit;
            } else {
                Alert::error('Login Failed, Please Contact Administrator')->showConfirmButton('OK');
                if (url()->previous()) {
                    return redirect()->back();
                } else {
                    return redirect('https://kpncorporation.darwinbox.com/');
                }
            }
        } else {
            Alert::error('Login Failed, Please Contact Administrator')->showConfirmButton('OK');
            if (url()->previous()) {
                return redirect()->back();
            } else {
                return redirect('https://kpncorporation.darwinbox.com/');
            }
        }
    }


    private function xorDecrypt($data, $key) {
        $keyLength = strlen($key);
        $dataLength = strlen($data);
        $decrypted = '';
    
        // Loop melalui data dan melakukan XOR dengan key
        for ($i = 0; $i < $dataLength; $i++) {
            $decrypted .= $data[$i] ^ $key[$i % $keyLength];
        }
    
        return $decrypted;
    }
}
