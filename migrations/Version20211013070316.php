<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211013070316 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tchat DROP FOREIGN KEY FK_8EA99CA4CD53EDB6');
        $this->addSql('ALTER TABLE tchat DROP FOREIGN KEY FK_8EA99CA4F624B39D');
        $this->addSql('DROP INDEX UNIQ_8EA99CA4F624B39D ON tchat');
        $this->addSql('DROP INDEX UNIQ_8EA99CA4CD53EDB6 ON tchat');
        $this->addSql('ALTER TABLE tchat DROP receiver_id, DROP sender_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tchat ADD receiver_id INT NOT NULL, ADD sender_id INT NOT NULL');
        $this->addSql('ALTER TABLE tchat ADD CONSTRAINT FK_8EA99CA4CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tchat ADD CONSTRAINT FK_8EA99CA4F624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8EA99CA4F624B39D ON tchat (sender_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8EA99CA4CD53EDB6 ON tchat (receiver_id)');
    }
}
