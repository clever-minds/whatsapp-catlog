<?php
$config = [
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'config' => 'C:/xampp_new/apache/conf/openssl.cnf'
];
if (!file_exists($config['config'])) {
    $config['config'] = 'C:/xampp_new/php/extras/ssl/openssl.cnf';
}
$res = openssl_pkey_new($config);
if (!$res) {
    echo "Failed to generate key.\n";
    exit(1);
}
openssl_pkey_export($res, $privKey);
$pubKey = openssl_pkey_get_details($res)['key'];
if (!is_dir('storage/app/keys')) {
    mkdir('storage/app/keys', 0777, true);
}
file_put_contents('storage/app/keys/private.pem', $privKey);
file_put_contents('storage/app/keys/public.pem', $pubKey);
echo "Keys successfully generated.\n";
