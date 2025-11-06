<?php

namespace App\Core\Infraestructure\Repositories\Command;

use App\Core\Domain\Entities\Career;
use App\Core\Domain\Repositories\Command\CareerRepInterface;
use App\Models\Career as EloquentCareer;
use App\Core\Infraestructure\Mappers\CareerMapper;

class EloquentCareerRepository implements CareerRepInterface{

    public function findByName(string $careerName): ?Career
    {
        $career = EloquentCareer::where('career_name', $careerName)->first();
        return $career ? CareerMapper::toDomain($career) : null;
    }

    public function findAll():array
    {
        return EloquentCareer::all()
        ->map(fn($career) => CareerMapper::toDomain($career))
        ->toArray();
    }

    public function findById(int $id): ?Career
    {
        return optional(EloquentCareer::find($id), fn($career) => CareerMapper::toDomain($career));

    }

}
