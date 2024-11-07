<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * RegistrationStep
 *
 * @property RegistrationFileConfiguration[] $registrationFileConfigurations
 * @property RegistrationFieldConfiguration[] $registrationFieldConfigurations
 *
 * @ORM\Table(name="registration_step")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\HasLifecycleCallbacks
 */
class RegistrationStep extends \MapasCulturais\Entity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="registration_step_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    protected $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="display_order", type="integer")
     */
    protected $displayOrder = 0;

    /**
     * @var \MapasCulturais\Entities\Opportunity
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Opportunity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="opportunity_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $opportunity;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime")
     */
    protected $createTimestamp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_timestamp", type="datetime")
     */
    protected $updateTimestamp;

    static function getControllerId() {
        return 'registrationstep';
    }

    function setOpportunity(int|Opportunity $opportunity) {
        if(is_int($opportunity)) {
            $app = \MapasCulturais\App::i();
            $opportunity = $app->repo('Opportunity')->find($opportunity);
        }
        $this->opportunity = $opportunity;
    }

    function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        $data['opportunity'] = $this->opportunity->simplify('id,name');
        return $data;
    }
}
