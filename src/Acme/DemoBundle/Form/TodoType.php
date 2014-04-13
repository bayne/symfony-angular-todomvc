<?php

namespace Acme\DemoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TodoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'textarea', array('description' => 'An item that needs to be completed',))
            ->add('completed', 'checkbox', array('description' => 'If the item is completed or not', 'required' => false))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Acme\DemoBundle\Model\Todo',
            'intention'          => 'todo',
            'translation_domain' => 'AcmeDemoBundle'
        ));
    }

    public function getName()
    {
        return 'todo';
    }
}
