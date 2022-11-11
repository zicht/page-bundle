<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Remove aliases referencing pages that do not exist anymore.
 */
class CleanAliasCommand extends Command
{
    protected static $defaultName = 'zicht:page:clean:alias';

    /** @var ManagerRegistry */
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine, string $name = null)
    {
        parent::__construct($name);
        $this->doctrine = $doctrine;
    }

    protected function configure()
    {
        $this
            ->addArgument('locale', InputArgument::REQUIRED, 'Page locale to clean')
            ->setDescription('Clean aliases that are not attached to pages any more.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $locale = $input->getArgument('locale');

        /** @var Connection $connection */
        $connection = $this->doctrine->getConnection();
        $stmt = $connection->prepare(
            'SELECT 
                page.id as original_page_id, 
                url_alias.id, 
                url_alias.internal_url, 
                url_alias.public_url, 
                REPLACE(url_alias.internal_url, :url_pre_id, "") AS page_id 
            FROM url_alias 
            LEFT JOIN page ON page.id = REPLACE(url_alias.internal_url, :url_pre_id, \'\') 
            WHERE internal_url LIKE :url_like
            AND page.id IS NULL'
        );
        $stmt->execute(
            [
                ':url_pre_id' => sprintf('/%s/page/', $locale),
                ':url_like' => sprintf('/%s/page/', sprintf('/%s/page/', $locale) . '%'),
            ]
        );

        $table = new Table($output);
        $table->setHeaders(['Alias', 'Internal URL', 'Stripped Id', 'Original Page Id']);

        $records = $stmt->fetchAll();

        if (!count($records)) {
            $output->writeln('No obsolete aliases found!');
            return 0;
        }

        foreach ($records as $record) {
            $table->addRow(
                [
                    $record['public_url'],
                    $record['internal_url'],
                    $record['page_id'],
                    $record['original_page_id'],
                ]
            );
        }

        $table->render();

        $helper = $this->getHelper('question');
        $confirm = new ConfirmationQuestion('Would you like to remove the above obsolete url aliases? [Y/n] : ', false);

        if (!$helper->ask($input, $output, $confirm)) {
            $output->writeln('Okay, bye');
            return 0;
        }

        $stmt->execute();

        $aliasIds = [];
        foreach ($stmt->fetchAll() as $record) {
            $aliasIds[] = $record['id'];
        }

        $connection->query(sprintf('DELETE FROM url_alias WHERE id IN (%s)', implode(', ', $aliasIds)));
        $output->writeln(sprintf('Removed %d aliases', count($records)));

        return 0;
    }
}
