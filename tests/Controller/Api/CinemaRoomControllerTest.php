<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Entity\CinemaRoom;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CinemaRoomControllerTest extends WebTestCase
{
    private static bool $schemaCreated = false;

    private const VALID_BODY = [
        'rows' => 5,
        'columns' => 10,
        'movie' => 'Example Movie',
        'movieDatetime' => '2025-12-01T20:00:00+00:00',
    ];

    public function testCreateCinemaRoomSuccess(): void
    {
        $client = static::createClient();
        $this->ensureSchema($client);
        $token = $this->createJwtToken($client);
        $client->request(
            'POST',
            '/api/cinema-rooms',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
            json_encode(self::VALID_BODY)
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $content = $client->getResponse()->getContent();
        $data = json_decode($content, true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('id', $data['data']);
        $this->assertSame(5, $data['data']['rows']);
        $this->assertSame(10, $data['data']['columns']);
        $this->assertSame('Example Movie', $data['data']['movie']);
        $this->assertArrayHasKey('movieDatetime', $data['data']);

        $id = $data['data']['id'];
        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        $room = $em->getRepository(CinemaRoom::class)->find($id);
        $this->assertInstanceOf(CinemaRoom::class, $room);
        $this->assertSame(5, $room->getRows());
        $this->assertSame(10, $room->getColumns());
        $this->assertSame('Example Movie', $room->getMovie());
        $this->assertNotNull($room->getMovieDatetime());
        $this->assertNotNull($room->getCreation());
        $this->assertNotNull($room->getUpdated());
    }

    public function testCreateCinemaRoomWithInvalidDateTimeReturnsValidationError(): void
    {
        $client = static::createClient();
        $this->ensureSchema($client);
        $token = $this->createJwtToken($client);

        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $countBefore = $em->getRepository(CinemaRoom::class)->count([]);

        $invalidBody = array_merge(self::VALID_BODY, ['movieDatetime' => 'not-a-valid-datetime']);
        $client->request(
            'POST',
            '/api/cinema-rooms',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
            json_encode($invalidBody)
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $content = $client->getResponse()->getContent();
        $data = json_decode($content, true);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('Validation failed', $data['error']);
        $this->assertArrayHasKey('messages', $data);
        $this->assertArrayHasKey('movieDatetime', $data['messages']);
        $this->assertNotEmpty($data['messages']['movieDatetime']);

        $em->clear();
        $countAfter = $em->getRepository(CinemaRoom::class)->count([]);
        $this->assertSame($countBefore, $countAfter, 'No cinema room should be created when movieDatetime is invalid');
    }

    private function ensureSchema(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): void
    {
        if (self::$schemaCreated) {
            return;
        }
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        if ($metadata !== []) {
            (new SchemaTool($em))->updateSchema($metadata);
            self::$schemaCreated = true;
        }
    }

    private function createJwtToken(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): string
    {
        $secret = $client->getContainer()->getParameter('kernel.secret');
        $key = strlen($secret) < 32 ? hash('sha256', $secret, true) : $secret;
        $payload = [
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        return JWT::encode($payload, $key, 'HS256');
    }
}
