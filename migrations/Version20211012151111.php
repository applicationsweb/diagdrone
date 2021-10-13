<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211012151111 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tchat (id INT AUTO_INCREMENT NOT NULL, receiver_id INT NOT NULL, sender_id INT NOT NULL, message LONGTEXT NOT NULL, created DATETIME NOT NULL, UNIQUE INDEX UNIQ_8EA99CA4CD53EDB6 (receiver_id), UNIQUE INDEX UNIQ_8EA99CA4F624B39D (sender_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tchat ADD CONSTRAINT FK_8EA99CA4CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tchat ADD CONSTRAINT FK_8EA99CA4F624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE tchat');
    }
}
