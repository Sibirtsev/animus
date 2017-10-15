<?php
namespace ApartmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use ApartmentBundle\Entity\Apartment;

class ApartmentType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Apartment::class,
        ));
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('moveInDate')
            ->add('street')
            ->add('town')
            ->add('country')
            ->add('postCode')
            ->add('email')
            ->add('save', SubmitType::class)
        ;
    }
}