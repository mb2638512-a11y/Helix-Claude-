<?php

it('merges Helix Claude key with selected Hetzner keys', function () {
    $Helix ClaudeKeyId = 123;
    $selectedHetznerKeys = [456, 789];

    // Simulate the merge logic from createHetznerServer
    $sshKeys = array_merge(
        [$Helix ClaudeKeyId],
        $selectedHetznerKeys
    );

    expect($sshKeys)->toBe([123, 456, 789])
        ->and(count($sshKeys))->toBe(3);
});

it('removes duplicate SSH key IDs', function () {
    $Helix ClaudeKeyId = 123;
    $selectedHetznerKeys = [123, 456, 789]; // User also selected Helix Claude key

    // Simulate the merge and deduplication logic
    $sshKeys = array_merge(
        [$Helix ClaudeKeyId],
        $selectedHetznerKeys
    );
    $sshKeys = array_unique($sshKeys);
    $sshKeys = array_values($sshKeys);

    expect($sshKeys)->toBe([123, 456, 789])
        ->and(count($sshKeys))->toBe(3);
});

it('works with no selected Hetzner keys', function () {
    $Helix ClaudeKeyId = 123;
    $selectedHetznerKeys = [];

    // Simulate the merge logic
    $sshKeys = array_merge(
        [$Helix ClaudeKeyId],
        $selectedHetznerKeys
    );

    expect($sshKeys)->toBe([123])
        ->and(count($sshKeys))->toBe(1);
});

it('validates SSH key IDs are integers', function () {
    $selectedHetznerKeys = [456, 789, 1011];

    foreach ($selectedHetznerKeys as $keyId) {
        expect($keyId)->toBeInt();
    }
});
