<?php

namespace Acme\DemoBundle\Tests\Controller;

use Bazinga\Bundle\RestExtraBundle\Test\WebTestCase;
use FOS\RestBundle\Util\Codes;
use Symfony\Component\BrowserKit\Client;

class TodoControllerTest extends WebTestCase
{
    public function testGetTodos()
    {
        $client = $this->getClient(true);

        // head request
        $client->request('HEAD', '/todos.json');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());

        // empty list
        $client->request('GET', '/todos.json');
        $response = $client->getResponse();

        $this->assertJsonResponse($response);
        $this->assertEquals('{"todos":[],"limit":5,"_links":{"self":{"href":"http:\/\/localhost\/todos"},"todo":{"href":"http:\/\/localhost\/todos\/{id}","templated":true}}}', $response->getContent());

        // list
        $this->createTodo($client, 'my todo for list');

        $client->request('GET', '/todos.json');
        $response = $client->getResponse();

        $this->assertJsonResponse($response);
        $contentWithoutSecret = preg_replace('/"secret":"[^"]*"/', '"secret":"XXX"', $response->getContent());
        $this->assertEquals('{"todos":[{"secret":"XXX","message":"my todo for list","_links":{"self":{"href":"http:\/\/localhost\/todos\/0"}}}],"limit":5,"_links":{"self":{"href":"http:\/\/localhost\/todos"},"todo":{"href":"http:\/\/localhost\/todos\/{id}","templated":true}}}', $contentWithoutSecret);
    }

    public function testGetTodo()
    {
        $client = $this->getClient(true);

        $client->request('GET', '/todos/0.json');
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"code":404,"message":"Todo does not exist."}', $response->getContent());

        $this->createTodo($client, 'my todo for get');

        $client->request('GET', '/todos/0.json');
        $response = $client->getResponse();

        $this->assertJsonResponse($response);
        $contentWithoutSecret = preg_replace('/"secret":"[^"]*"/', '"secret":"XXX"', $response->getContent());
        $this->assertEquals('{"secret":"XXX","message":"my todo for get","_links":{"self":{"href":"http:\/\/localhost\/todos\/0"}}}', $contentWithoutSecret);
    }

    public function testNewTodo()
    {
        $client = $this->getClient(true);

        $client->request('GET', '/todos/new.json');
        $response = $client->getResponse();

        $this->assertJsonResponse($response);
        $this->assertEquals('{"children":{"message":[]}}', $response->getContent());
    }

    public function testPostTodo()
    {
        $client = $this->getClient(true);

        $this->createTodo($client, 'my todo for post');

        $response = $client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_CREATED);
        $this->assertEquals($response->headers->get('location'), 'http://localhost/todos/0');
    }

    public function testEditTodo()
    {
        $client = $this->getClient(true);

        $client->request('GET', '/todos/0/edit.json');
        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"code":404,"message":"Todo does not exist."}', $response->getContent());

        $this->createTodo($client, 'my todo for post');

        $client->request('GET', '/todos/0/edit.json');
        $response = $client->getResponse();

        $this->assertJsonResponse($response);
        $this->assertEquals('{"children":{"message":[]}}', $response->getContent());
    }

    public function testPutShouldModifyATodo()
    {
        $client = $this->getClient(true);

        $client->request('PUT', '/todos/0.json', array(
            'todo' => array(
                'message' => ''
            )
        ));
        $response = $client->getResponse();

        $this->assertEquals(400, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"code":400,"message":"Validation Failed","errors":{"children":{"message":{"errors":["This value should not be blank."]}}}}', $response->getContent());

        $this->createTodo($client, 'my todo for post');

        $client->request('PUT', '/todos/0.json', array(
            'todo' => array(
                'message' => 'my todo for put'
            )
        ));
        $response = $client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_NO_CONTENT);
        $this->assertEquals($response->headers->get('location'), 'http://localhost/todos/0');
    }

    public function testPutShouldCreateATodo()
    {
        $client = $this->getClient(true);

        $client->request('PUT', '/todos/0.json', array(
            'todo' => array(
                'message' => ''
            )
        ));
        $response = $client->getResponse();

        $this->assertEquals(400, $response->getStatusCode(), $response->getContent());
        $this->assertEquals('{"code":400,"message":"Validation Failed","errors":{"children":{"message":{"errors":["This value should not be blank."]}}}}', $response->getContent());

        $client->request('PUT', '/todos/0.json', array(
            'todo' => array(
                'message' => 'my todo for put'
            )
        ));
        $response = $client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_CREATED);
        $this->assertEquals($response->headers->get('location'), 'http://localhost/todos/0');
    }

    public function testRemoveTodo()
    {
        $client = $this->getClient(true);

        $client->request('GET', '/todos/0/remove.json');
        $response = $client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_NOT_FOUND);

        $this->createTodo($client, 'my todo for get');

        $client->request('GET', '/todos/0/remove.json');
        $response = $client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_NO_CONTENT);
        $this->assertTrue($response->headers->contains('location', 'http://localhost/todos'));
    }

    public function testDeleteTodo()
    {
        $client = $this->getClient(true);

        $client->request('DELETE', '/todos/0.json');
        $response = $client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_NOT_FOUND);

        $this->createTodo($client, 'my todo for get');

        $client->request('DELETE', '/todos/0.json');
        $response = $client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_NO_CONTENT);
        $this->assertTrue($response->headers->contains('location', 'http://localhost/todos'));
    }

    protected function createTodo(Client $client, $message)
    {
        $client->request('POST', '/todos.json', array(
            'todo' => array(
                'message' => $message
            )
        ));
        $response = $client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_CREATED);
    }

    private function getClient($authenticated = false)
    {
        $params = array();
        if ($authenticated) {
            $params = array_merge($params, array(
                    'PHP_AUTH_USER' => 'restapi',
                    'PHP_AUTH_PW'   => 'secretpw',
                ));
        }

        return static::createClient(array(), $params);
    }
}
