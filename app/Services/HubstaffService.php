<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class HubstaffService
{
    protected $baseUrl = 'https://api.hubstaff.com/v2';

    protected function getAccessToken()
    {
        $token = Cache::get('hubstaff_access_token');

        if (!$token) {
            throw new Exception('No hay token de acceso disponible. Por favor, conecte su cuenta de Hubstaff.');
        }

        return $token;
    }

    public function createTask(array $data)
    {
        try {
            $response = Http::withOptions([
                'verify' => !app()->environment('local')
            ])->withToken($this->getAccessToken())
                ->post("{$this->baseUrl}/projects/{$data['project_id']}/tasks", [
                    'task' => [
                        'summary' => $data['title'],
                        'description' => $data['description'] ?? '',
                        'assignee_id' => $data['assignee_id'] ?? null,
                        'due_date' => $data['due_date'] ?? null,
                        'status' => $data['status'] ?? 'open'
                    ]
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Error al crear la tarea en Hubstaff: ' . $response->body());
        } catch (Exception $e) {
            throw new Exception('Error en el servicio de Hubstaff: ' . $e->getMessage());
        }
    }
}
