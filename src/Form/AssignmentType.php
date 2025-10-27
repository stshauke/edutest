<?php

namespace App\Form;

use App\Entity\Assignment;
use App\Entity\Exam;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Sélection de l'examen
            ->add('exam', EntityType::class, [
                'class' => Exam::class,
                'choice_label' => 'title',
                'label' => 'Examen à affecter',
                'attr' => ['class' => 'form-select'],
                'placeholder' => 'Sélectionner un examen',
            ])

            // Sélection de l'étudiant
            ->add('student', EntityType::class, [
                'class' => User::class,
                'choice_label' => fn(User $u) => sprintf('%s (%s)', $u->getFullName() ?: 'Utilisateur', $u->getEmail()),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_STUDENT%')
                        ->orderBy('u.fullName', 'ASC');
                },
                'label' => 'Étudiant',
                'attr' => ['class' => 'form-select'],
                'placeholder' => 'Sélectionner un étudiant',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Assignment::class,
        ]);
    }
}
