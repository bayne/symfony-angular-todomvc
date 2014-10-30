<?php

namespace Acme\DemoBundle\Controller;

use Acme\DemoBundle\Entity\Todo;
use Acme\DemoBundle\Form\TodoType;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\RouteRedirectView;
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
     */
    public function getTodosAction()
    {
        $data = ['todos' => $this->getDoctrine()->getRepository('AcmeDemoBundle:Todo')->findAll()];
        $view = $this->view($data, 200)
            ->setTemplate('AcmeDemoBundle:Todo:getTodos.html.twig')
        ;

        return $this->handleView($view);
    }

    /**
     * Get a single todo.
     *
     * @ApiDoc(
     *   output = "Acme\DemoBundle\Entity\Todo",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the todo is not found"
     *   }
     * )
     *
     * @param int $id the todo id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws NotFoundHttpException when todo not exist
     */
    public function getTodoAction($id)
    {
        $data = $this->getDoctrine()->getRepository('AcmeDemoBundle:Todo')->find($id);
        if (!$data) {
            throw $this->createNotFoundException("Todo does not exist.");
        }
        $view = $this->view($data, 200)
            ->setTemplate('AcmeDemoBundle:Todo:getTodo.html.twig')
            ->setTemplateVar('todo')
        ;

        return $this->handleView($view);
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
     * @return FormTypeInterface
     */
    public function newTodoAction()
    {
        $view = $this->view(
                $this->createForm(new TodoType()),
                200
            )
            ->setTemplate('AcmeDemoBundle:Todo:newTodo.html.twig')
            ->setTemplateVar('todo')
        ;
        return $this->handleView($view);
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
     * @param Request $request the request object
     *
     * @return FormTypeInterface|RouteRedirectView
     */
    public function postTodosAction(Request $request)
    {
        $todo = new Todo();
        $form = $this->createForm(new TodoType(), $todo);

        $form->submit($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($todo);
            $em->flush();
            $view = $this
                ->routeRedirectView('get_todo', ['id' => $todo->getId()])
                ->setTemplate('AcmeDemoBundle:Todo:getTodo.html.twig')
                ->setTemplateVar('todo')
                ->setData($todo)
            ;
        } else {
            $view = $this->view(['form' => $form], 400);
        }

        return $this->handleView($view);
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
     * @param int $id the todo id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws NotFoundHttpException when todo not exist
     */
    public function editTodosAction($id)
    {
        $todo = $this->getDoctrine()->getRepository('AcmeDemoBundle:Todo')->find($id);
        if (!$todo) {
            throw $this->createNotFoundException("Todo does not exist.");
        }

        $form = $this->createForm(new TodoType(), $todo);

        $view = $this->view($form, 200)
            ->setTemplate('AcmeDemoBundle:Todo:editTodo.html.twig')
            ->setTemplateVar('form')
        ;

        return $this->handleView($view);
    }

    /**
     * Update existing todo from the submitted data or create a new todo at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "Acme\DemoBundle\Form\TodoType",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param Request $request the request object
     * @param int $id the todo id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws NotFoundHttpException when todo not exist
     */
    public function putTodosAction(Request $request, $id)
    {
        $todo = $this->getDoctrine()->getRepository('AcmeDemoBundle:Todo')->find($id);
        if (!$todo) {
            throw $this->createNotFoundException("Todo does not exist.");
        }

        $form = $this->createForm(new TodoType(), $todo);

        $form->submit($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($todo);
            $em->flush();
            $view = $this
                ->routeRedirectView('get_todo', ['id' => $todo->getId()])
                ->setTemplate('AcmeDemoBundle:Todo:getTodo.html.twig')
                ->setTemplateVar('todo')
                ->setData($todo)
            ;
        } else {
            $view = $this->view(['form' => $form], 400);
        }

        return $this->handleView($view);
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
     * @param int $id the todo id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws NotFoundHttpException when todo not exist
     */
    public function deleteTodosAction($id)
    {
        $todo = $this->getDoctrine()->getRepository('AcmeDemoBundle:Todo')->find($id);
        if (!$todo) {
            throw $this->createNotFoundException("Todo does not exist.");
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($todo);
        $em->flush();

        return $this->handleView(
            $this->routeRedirectView('get_todos', [], Codes::HTTP_NO_CONTENT)
        );
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
     * @param int $id the todo id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws NotFoundHttpException when todo not exist
     */
    public function removeTodosAction($id)
    {
        return $this->deleteTodosAction($id);
    }

}
