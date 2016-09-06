<?php

namespace PhpProjects\SocialDev\UI\FormTypes;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form type for adding a new url to the system.
 */
class UrlFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('url', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
            'label' => 'Share a Url',
            'required' => true,
            'attr' => [
                'placeholder' => 'Url',
            ],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Url(),
            ],
        ]);
    }

    
}