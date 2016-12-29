<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class CleanAliasCommand
 *
 * Remove aliases referencing pages that do not exist anymore.
 */
class CleanAliasCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('zicht:page:clean:alias')
            ->addArgument('locale', InputArgument::REQUIRED, 'Page locale to clean')
            ->setDescription('Clean aliases that are not attached to pages any more.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locale = $input->getArgument('locale');

        /** @var Connection $connection */
        $connection = $this->getContainer()->get('doctrine')->getConnection();
        $stmt = $connection->query(
            sprintf(
                'SELECT 
                    page.id as original_page_id, 
                    url_alias.id, 
                    url_alias.internal_url, 
                    url_alias.public_url, 
                    REPLACE(url_alias.internal_url, "/%1$s/page/", "") AS page_id 
                FROM url_alias 
                LEFT JOIN page ON page.id = REPLACE(url_alias.internal_url, \'/%1$s/page/\', \'\') 
                WHERE internal_url LIKE "/%s/page/%%%%"
                AND page.id IS NULL',
                $locale
            )
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
