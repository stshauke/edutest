<?php

namespace App\Form;

use App\Entity\Exam;
use App\Entity\Question;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ✅ Correction : 
 *  - Supprime l'import de App\Form\ChoiceType (on le renomme autrement)
 *  - Utilise correctement le ChoiceType natif de Symfony
 *  - Ajoute la collection de sous-formulaires pour les QCM
 */
class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', null, [
                'label' => 'Intitulé de la question',
                'attr' => [
                    'placeholder' => 'Ex : Quelle est la capitale de la France ?',
                    'class' => 'form-control',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de question',
                'choices' => [
                    'QCM (choix multiples)' => 'QCM',
                    'Réponse texte libre' => 'TEXT',
                ],
                'expanded' => false, // ✅ Corrige l'erreur Twig
                'multiple' => false,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('points', IntegerType::class, [
                'label' => 'Points attribués',
                'attr' => [
                    'min' => 1,
                    'placeholder' => 'Ex : 5',
                    'class' => 'form-control',
                ],
            ])
            ->add('exam', EntityType::class, [
                'class' => Exam::class,
                'choice_label' => 'title',
                'label' => 'Examen associé',
                'placeholder' => '— Sélectionner un examen —',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Description ou consigne (facultatif)',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Ex : Sélectionnez toutes les réponses correctes.',
                    'class' => 'form-control',
                ],
            ])
            // ✅ Ici on change le nom pour éviter la collision
            ->add('choices', CollectionType::class, [
                'entry_type' => \App\Form\ChoiceQcmType::class, // ✅ Nouveau nom pour ton sous-formulaire
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Choix possibles (pour QCM uniquement)',
                'prototype' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
