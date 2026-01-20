<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Reporte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_reporte = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $titulo;

    #[ORM\Column(type: 'text')]
    private string $descripcion;

    #[ORM\Column(type: 'string', length: 40)]
    private string $tipo;

    #[ORM\ManyToOne(targetEntity: Curso::class)]
    #[ORM\JoinColumn(name: 'id_curso', referencedColumnName: 'id_curso')]
    private Curso $curso;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_docente', referencedColumnName: 'id')]
    private User $docente;

    #[ORM\ManyToOne(targetEntity: PeriodoLectivo::class)]
    #[ORM\JoinColumn(name: 'id_periodo', referencedColumnName: 'id_periodo')]
    private PeriodoLectivo $periodo;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $fecha_creacion;

    public function getIdReporte(): ?int
    {
        return $this->id_reporte;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;
        return $this;
    }

    public function getCurso(): Curso
    {
        return $this->curso;
    }

    public function setCurso(Curso $curso): self
    {
        $this->curso = $curso;
        return $this;
    }

    public function getDocente(): User
    {
        return $this->docente;
    }

    public function setDocente(User $docente): self
    {
        $this->docente = $docente;
        return $this;
    }

    public function getPeriodo(): PeriodoLectivo
    {
        return $this->periodo;
    }

    public function setPeriodo(PeriodoLectivo $periodo): self
    {
        $this->periodo = $periodo;
        return $this;
    }

    public function getFechaCreacion(): \DateTimeInterface
    {
        return $this->fecha_creacion;
    }

    public function setFechaCreacion(\DateTimeInterface $fecha_creacion): self
    {
        $this->fecha_creacion = $fecha_creacion;
        return $this;
    }
}
