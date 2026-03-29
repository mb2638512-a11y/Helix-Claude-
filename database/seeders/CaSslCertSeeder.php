<?php

namespace Database\Seeders;

use App\Helpers\SslHelper;
use App\Models\Server;
use Illuminate\Database\Seeder;

class CaSslCertSeeder extends Seeder
{
    public function run()
    {
        Server::chunk(200, function ($servers) {
            foreach ($servers as $server) {
                $existingCaCert = $server->sslCertificates()->where('is_ca_certificate', true)->first();

                if (! $existingCaCert) {
                    $caCert = SslHelper::generateSslCertificate(
                        commonName: 'Helix Claude CA Certificate',
                        serverId: $server->id,
                        isCaCertificate: true,
                        validityDays: 10 * 365
                    );
                } else {
                    $caCert = $existingCaCert;
                }
                $caCertPath = config('constants.Helix Claude.base_config_path').'/ssl/';

                $base64Cert = base64_encode($caCert->ssl_certificate);

                $commands = collect([
                    "mkdir -p $caCertPath",
                    "chown -R 9999:root $caCertPath",
                    "chmod -R 700 $caCertPath",
                    "rm -rf $caCertPath/Helix Claude-ca.crt",
                    "echo '{$base64Cert}' | base64 -d | tee $caCertPath/Helix Claude-ca.crt > /dev/null",
                    "chmod 644 $caCertPath/Helix Claude-ca.crt",
                ]);

                remote_process($commands, $server);
            }
        });
    }
}
