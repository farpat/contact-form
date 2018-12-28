<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{EmailType, TextareaType, TextType};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactType extends AbstractType
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct (TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm (FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['attr' => ['placeholder' => $this->translator->trans('Your name')]])
            ->add('email', EmailType::class, ['attr' => ['placeholder' => $this->translator->trans('Your e-mail address')]])
            ->add('question', TextareaType::class, ['attr' => ['placeholder' => $this->translator->trans('Your question')]]);
    }

    public function configureOptions (OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
