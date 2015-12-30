<?php
/**
 * Created by JetBrains PhpStorm.
 * User: splancon
 * Date: 17/07/13
 * Time: 11:00
 * To change this template use File | Settings | File Templates.
 */

namespace Bigfoot\Bundle\ImportBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProtocolType
 * @package Bigfoot\Bundle\ImportBundle\Form
 * @author S.Plançon s.plancon@c2is.fr
 */
class ProtocolType extends AbstractType {

    /**
     * @var array
     */
    private $protocolChoices;

    /**
     * @param array $protocolChoices
     */
    public function __construct(array $protocolChoices)
    {
        $this->protocolChoices = $protocolChoices;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->protocolChoices
        ));
    }

    /**
     * Set parent type
     *
     * @return string
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * Set the name
     *
     * @return string
     */
    public function getName()
    {
        return 'protocol';
    }
}