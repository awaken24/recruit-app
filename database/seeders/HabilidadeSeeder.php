<?php

namespace Database\Seeders;

use App\Models\Habilidade;
use Illuminate\Database\Seeder;

class HabilidadeSeeder extends Seeder
{
    public function run()
    {
        $habilidades = [
            'JavaScript',
            'Python',
            'Java',
            'PHP',
            'C#',
            'Ruby',
            'SQL',
            'HTML',
            'CSS',
            'React',
            'Angular',
            'Vue.js',
            'Node.js',
            'Laravel',
            'Spring Boot',
            'Django',
            'Docker',
            'AWS',
            'Git',
            'MongoDB',
            'PostgreSQL',
            'MySQL',
            'TypeScript',
            'SCRUM',
            'Redux',
            'REST API'
        ];

        foreach ($habilidades as $habilidade) {
            Habilidade::create(['nome' => $habilidade]);
        }
    }
}
