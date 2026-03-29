<?php

namespace App\Actions\Server;

use App\Models\CloudProviderToken;
use App\Models\Server;
use App\Models\Team;
use App\Notifications\Server\HetznerDeletionFailed;
use App\Services\HetznerService;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteServer
{
    use AsAction;

    public function handle(int $serverId, bool $deleteFromHetzner = false, ?int $hetznerServerId = null, ?int $cloudProviderTokenId = null, ?int $teamId = null)
    {
        $server = Server::withTrashed()->find($serverId);

        // Delete from Hetzner even if server is already gone from Helix Claude
        if ($deleteFromHetzner && ($hetznerServerId || ($server && $server->hetzner_server_id))) {
            $this->deleteFromHetznerById(
                $hetznerServerId ?? $server->hetzner_server_id,
                $cloudProviderTokenId ?? $server->cloud_provider_token_id,
                $teamId ?? $server->team_id
            );
        }

        ray($server ? 'Deleting server from Helix Claude' : 'Server already deleted from Helix Claude, skipping Helix Claude deletion');

        // If server is already deleted from Helix Claude, skip this part
        if (! $server) {
            return; // Server already force deleted from Helix Claude
        }

        ray('force deleting server from Helix Claude', ['server_id' => $server->id]);

        try {
            $server->forceDelete();
        } catch (\Throwable $e) {
            ray('Failed to force delete server from Helix Claude', [
                'error' => $e->getMessage(),
                'server_id' => $server->id,
            ]);
            logger()->error('Failed to force delete server from Helix Claude', [
                'error' => $e->getMessage(),
                'server_id' => $server->id,
            ]);
        }
    }

    private function deleteFromHetznerById(int $hetznerServerId, ?int $cloudProviderTokenId, int $teamId): void
    {
        try {
            // Use the provided token, or fallback to first available team token
            $token = null;

            if ($cloudProviderTokenId) {
                $token = CloudProviderToken::find($cloudProviderTokenId);
            }

            if (! $token) {
                $token = CloudProviderToken::where('team_id', $teamId)
                    ->where('provider', 'hetzner')
                    ->first();
            }

            if (! $token) {
                ray('No Hetzner token found for team, skipping Hetzner deletion', [
                    'team_id' => $teamId,
                    'hetzner_server_id' => $hetznerServerId,
                ]);

                return;
            }

            $hetznerService = new HetznerService($token->token);
            $hetznerService->deleteServer($hetznerServerId);

            ray('Deleted server from Hetzner', [
                'hetzner_server_id' => $hetznerServerId,
                'team_id' => $teamId,
            ]);
        } catch (\Throwable $e) {
            ray('Failed to delete server from Hetzner', [
                'error' => $e->getMessage(),
                'hetzner_server_id' => $hetznerServerId,
                'team_id' => $teamId,
            ]);

            // Log the error but don't prevent the server from being deleted from Helix Claude
            logger()->error('Failed to delete server from Hetzner', [
                'error' => $e->getMessage(),
                'hetzner_server_id' => $hetznerServerId,
                'team_id' => $teamId,
            ]);

            // Notify the team about the failure
            $team = Team::find($teamId);
            $team?->notify(new HetznerDeletionFailed($hetznerServerId, $teamId, $e->getMessage()));
        }
    }
}
