<?php

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $answerText = null;

    #[ORM\Column(nullable: true)]
    private ?float $pointsAwarded = null;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    private ?Assignment $assignment = null;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    private ?Question $question = null;

    /**
     * @var Collection<int, Choice>
     */
    #[ORM\ManyToMany(targetEntity: Choice::class, inversedBy: 'answers')]
    private Collection $selectedChoices;

    public function __construct()
    {
        $this->selectedChoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnswerText(): ?string
    {
        return $this->answerText;
    }

    public function setAnswerText(?string $answerText): static
    {
        $this->answerText = $answerText;

        return $this;
    }

    public function getPointsAwarded(): ?float
    {
        return $this->pointsAwarded;
    }

    public function setPointsAwarded(?float $pointsAwarded): static
    {
        $this->pointsAwarded = $pointsAwarded;

        return $this;
    }

    public function getAssignment(): ?Assignment
    {
        return $this->assignment;
    }

    public function setAssignment(?Assignment $assignment): static
    {
        $this->assignment = $assignment;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return Collection<int, Choice>
     */
    public function getSelectedChoices(): Collection
    {
        return $this->selectedChoices;
    }

    public function addSelectedChoice(Choice $selectedChoice): static
    {
        if (!$this->selectedChoices->contains($selectedChoice)) {
            $this->selectedChoices->add($selectedChoice);
        }

        return $this;
    }

    public function removeSelectedChoice(Choice $selectedChoice): static
    {
        $this->selectedChoices->removeElement($selectedChoice);

        return $this;
    }
}
