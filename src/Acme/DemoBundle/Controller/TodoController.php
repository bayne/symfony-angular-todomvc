<?php

namespace Acme\DemoBundle\Controller;

use Acme\DemoBundle\Form\TodoType;
use Acme\DemoBundle\Model\Todo;
use Acme\DemoBundle\Model\TodoCollection;

use FOS\RestBundle\Util\Codes;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\RouteRedirectView;

use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Rest controller for todos
 *
 * @package Acme\DemoBundle\Controller
 * @author Gordon Franke <info@nevalon.de>
 */
class TodoController extends FOSRestController
{
    const SESSION_CONTEXT_TODO = 'todos';

    /**
     * List all todos.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing todos.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="5", description="How many todos to return.")
     *
     * @Annotations\View()
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getTodosAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        $session = $request->getSession();

        $offset = $paramFetcher->get('offset');
        $start = null == $offset ? 0 : $offset + 1;
        $limit = $paramFetcher->get('limit');

        $todos = $session->get(self::SESSION_CONTEXT_TODO, array());
        $todos = array_slice($todos, $start, $limit, true);

        return new TodoCollection($todos, $offset, $limit);
    }

    /**
     * Get a single todo.
     *
     * @ApiDoc(
     *   output = "Acme\DemoBundle\Model\Todo",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the todo is not found"
     *   }
     * )
     * 
     * @Annotations\View(templateVar="todo")
     *
     * @param Request $request the request object
     * @param int     $id      the todo id
     *
     * @return array
     *
     * @throws NotFoundHttpException when todo not exist
     */
    public function getTodoAction(Request $request, $id)
    {
        $session = $request->getSession();
        $todos   = $session->get(self::SESSION_CONTEXT_TODO);
        if (!isset($todos[$id])) {
            throw $this->createNotFoundException("Todo does not exist.");
        }

        $view = new View($todos[$id]);
        $group = $this->container->get('security.context')->isGranted('ROLE_API') ? 'restapi' : 'standard';
        $view->getSerializationContext()->setGroups(array('Default', $group));

        return $view;
    }

    /**
     * Presents the form to use to create a new todo.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\View()
     *
     * @return FormTypeInterface
     */
    public function newTodoAction()
    {
        return $this->createForm(new TodoType());
    }

    /**
     * Creates a new todo from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Acme\DemoBundle\Form\TodoType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template = "AcmeDemoBundle:Todo:newTodo.html.twig",
     *   statusCode = Codes::HTTP_BAD_REQUEST
     * )
     *
     * @param Request $request the request object
     *
     * @return FormTypeInterface|RouteRedirectView
     */
    public function postTodosAction(Request $request)
    {
        $session = $request->getSession();
        $todos   = $session->get(self::SESSION_CONTEXT_TODO);

        $todo = new Todo();
        $todo->id = $this->getValidIndex($todos);
        $form = $this->createForm(new TodoType(), $todo);

        $form->submit($request);
        if ($form->isValid()) {
            $todo->secret = base64_encode($this->get('security.secure_random')->nextBytes(64));
            $todos[$todo->id] = $todo;
            $session->set(self::SESSION_CONTEXT_TODO, $todos);

            $view = $this->routeRedirectView('get_todo', array('id' => $todo->id));
            $view->setData($todo);
            $group = $this->container->get('security.context')->isGranted('ROLE_API') ? 'restapi' : 'standard';
            $view->getSerializationContext()->setGroups(array('Default', $group));
            return $view;
        }

        return array(
            'form' => $form
        );
    }

    /**
     * Presents the form to use to update an existing todo.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes={
     *     200 = "Returned when successful",
     *     404 = "Returned when the todo is not found"
     *   }
     * )
     *
     * @Annotations\View()
     *
     * @param Request $request the request object
     * @param int     $id      the todo id
     *
     * @return FormTypeInterface
     *
     * @throws NotFoundHttpException when todo not exist
     */
    public function editTodosAction(Request $request, $id)
    {
        $session = $request->getSession();

        $todos = $session->get(self::SESSION_CONTEXT_TODO);
        if (!isset($todos[$id])) {
            throw $this->createNotFoundException("Todo does not exist.");
        }

        $form = $this->createForm(new TodoType(), $todos[$id]);

        return $form;
    }

    /**
     * Update existing todo from the submitted data or create a new todo at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Acme\DemoBundle\Form\TodoType",
     *   statusCodes = {
     *     201 = "Returned when a new resource is created",
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template="AcmeDemoBundle:Todo:editTodo.html.twig",
     *   templateVar="form"
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the todo id
     *
     * @return FormTypeInterface|RouteRedirectView
     *
     * @throws NotFoundHttpException when todo not exist
     */
    public function putTodosAction(Request $request, $id)
    {
        $session = $request->getSession();

        $todos   = $session->get(self::SESSION_CONTEXT_TODO);
        if (!isset($todos[$id])) {
            $todo = new Todo();
            $todo->id = $id;
            $statusCode = Codes::HTTP_CREATED;
        } else {
            $todo = $todos[$id];
            $statusCode = Codes::HTTP_NO_CONTENT;
        }

        $form = $this->createForm(new TodoType(), $todo);

        $form->submit($request);
        if ($form->isValid()) {
            if (!isset($todo->secret)) {
                $todo->secret = base64_encode($this->get('security.secure_random')->nextBytes(64));
            }
            $todos[$id] = $todo;
            $session->set(self::SESSION_CONTEXT_TODO, $todos);

            return $this->routeRedirectView('get_todo', array('id' => $todo->id), $statusCode);
        }

        return $form;
    }

    /**
     * Removes a todo.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes={
     *     204="Returned when successful",
     *     404="Returned when the todo is not found"
     *   }
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the todo id
     *
     * @return RouteRedirectView
     *
     * @throws NotFoundHttpException when todo not exist
     */
    public function deleteTodosAction(Request $request, $id)
    {
        $session = $request->getSession();
        $todos   = $session->get(self::SESSION_CONTEXT_TODO);
        if (!isset($todos[$id])) {
            throw $this->createNotFoundException("Todo does not exist.");
        }

        unset($todos[$id]);
        $session->set(self::SESSION_CONTEXT_TODO, $todos);

        return $this->routeRedirectView('get_todos', array(), Codes::HTTP_NO_CONTENT);
    }

    /**
     * Removes a todo.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes={
     *     204="Returned when successful",
     *     404="Returned when the todo is not found"
     *   }
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the todo id
     *
     * @return RouteRedirectView
     *
     * @throws NotFoundHttpException when todo not exist
     */
    public function removeTodosAction(Request $request, $id)
    {
        return $this->deleteTodosAction($request, $id);
    }

    /**
     * Get a valid index key.
     *
     * @param array $todos
     *
     * @return int $id
     */
    private function getValidIndex($todos)
    {
        $id = count($todos);
        while (isset($todos[$id])) {
            $id++;
        }

        return $id;
    }

}
