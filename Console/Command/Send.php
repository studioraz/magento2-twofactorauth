<?php
namespace SR\TwoFactorAuth\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use SR\TwoFactorAuth\Model\Services\SmsService\Unicell as Service;

use \Magento\Framework\ObjectManagerInterface;

class Send extends Command
{

    const SMS_PHONE_NUMBER = 'phone_number';

    const SMS_TEXT_MESSGAE = 'text_message';

    /**
     * @var Service
     */
    protected $smsService;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Send constructor.
     * @param Service $smsService
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Service $smsService,
        ObjectManagerInterface $objectManager
    )
    {
        $this->smsService = $smsService;
        $this->objectManager = $objectManager;

        parent::__construct();
    }


    protected function configure()
    {

        $this->setName("sr:unicellsms:send")
            ->setDescription("A command the programmer was too lazy to enter a description for.")
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * Get list of options and arguments for the command
     * @return array
     */
    public function getInputList()
    {
        return [
            new InputArgument(
                self::SMS_PHONE_NUMBER,
                InputArgument::REQUIRED,
                'SMS telephone number'
            ),
            new InputArgument(
                self::SMS_TEXT_MESSGAE,
                InputArgument::REQUIRED,
                'SMS text message'
            )
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->smsService
            ->setRecipients($input->getArgument(self::SMS_PHONE_NUMBER))
            ->setMessage($input->getArgument(self::SMS_TEXT_MESSGAE));

        $this->smsService->execute();

        $output->writeln(sprintf('Response code: %s, response message: %s',
            $this->smsService->getResponseCode(),
            $this->smsService->getResponseMessage()
        ));

    }


}