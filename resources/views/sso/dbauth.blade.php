<!DOCTYPE html>
<html>
<head>
    <title>DBAuth</title>
</head>
<body>

<h1>decryption SSO Darwinbox :</h1>
<p><b><span style="background-color: #FFFF00">STEP 1</span> : Variable Data</b> -> <span style="word-wrap: break-word">{{ $encryptedData }}</span></p>
<p><b><span style="background-color: #FFFF00">STEP 2</span> : Decryption using base64</b> -> {{ $decodedData }}</p>
<p><b><span style="background-color: #FFFF00">STEP 3</span> : Decryption using XOR</b> -> <br><span style="background-color: #7FFF00">XOR Key (provided by Darwinbox) : {{ $key }}</span><br>XOR Decryption : {{ $decryptedDataxor }}</p>
<p><b><span style="background-color: #FFFF00">STEP 4</span> : Decryption using base64</b> -> {{ $decryptedData }}<br>expected result : variable (email, timestamp,Uid,hash,employee no)</p>

</body>
</html>