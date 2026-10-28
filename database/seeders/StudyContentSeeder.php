<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudyContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->catalog() as $subjectIndex => $data) {
            $subject = Subject::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'order' => $subjectIndex + 1,
                ]
            );

            foreach ($data['topics'] as $topicIndex => $topicData) {
                $topic = Topic::firstOrCreate(
                    ['subject_id' => $subject->id, 'title' => $topicData['title']],
                    ['description' => $topicData['description'], 'order' => $topicIndex + 1]
                );

                foreach ($topicData['materials'] as $materialIndex => $material) {
                    Material::firstOrCreate(
                        ['topic_id' => $topic->id, 'title' => $material['title']],
                        [
                            'type' => $material['type'],
                            'content' => $material['content'],
                            'order' => $materialIndex + 1,
                        ]
                    );
                }

                if (isset($topicData['quiz'])) {
                    $this->seedQuiz($topic, $topicData['quiz']);
                }
            }
        }
    }

    /**
     * En las opciones, el prefijo * marca la(s) respuesta(s) correcta(s).
     */
    private function seedQuiz(Topic $topic, array $quizData): void
    {
        $quiz = Quiz::firstOrCreate(
            ['topic_id' => $topic->id],
            ['title' => $quizData['title']]
        );

        foreach ($quizData['questions'] as $index => $q) {
            $question = Question::firstOrCreate(
                ['quiz_id' => $quiz->id, 'statement' => $q['statement']],
                [
                    'type' => $q['type'],
                    'explanation' => $q['explanation'] ?? null,
                    'correct_answer_text' => $q['answer'] ?? null,
                    'order' => $index + 1,
                ]
            );

            if (! $question->wasRecentlyCreated) {
                continue;
            }

            foreach ($q['options'] ?? [] as $text) {
                $question->options()->create([
                    'text' => ltrim($text, '*'),
                    'is_correct' => str_starts_with($text, '*'),
                ]);
            }
        }
    }

    private function catalog(): array
    {
        return [
            [
                'name' => 'Matemáticas Básicas',
                'description' => 'Fundamentos de aritmética y álgebra.',
                'topics' => [
                    [
                        'title' => 'Suma y Resta',
                        'description' => 'Operaciones básicas con números enteros.',
                        'materials' => [
                            ['type' => 'text', 'title' => 'Introducción a la suma', 'content' => 'La suma es la operación que combina dos o más números para obtener un total...'],
                            ['type' => 'text', 'title' => 'Introducción a la resta', 'content' => 'La resta es la operación inversa a la suma...'],
                        ],
                        'quiz' => [
                            'title' => 'Quiz: Suma y Resta',
                            'questions' => [
                                ['type' => 'single_choice', 'statement' => '¿Cuánto es 5 + 3?', 'options' => ['7', '*8', '9'], 'explanation' => 'Sumar 5 + 3 significa contar 3 unidades más a partir del 5: 6, 7, 8.'],
                                ['type' => 'true_false', 'statement' => '¿Es correcto que 10 - 4 = 6?', 'options' => ['*Verdadero', 'Falso'], 'explanation' => '10 - 4 = 6 es correcto: al restar 4 unidades a 10, quedan 6.'],
                                ['type' => 'multiple_choice', 'statement' => '¿Cuáles de las siguientes sumas dan como resultado 10?', 'options' => ['*6 + 4', '5 + 6', '*7 + 3', '8 + 3'], 'explanation' => '6 + 4 = 10 y 7 + 3 = 10. Las otras dos no suman 10.'],
                                ['type' => 'short_answer', 'statement' => 'Escribe el resultado de 12 - 5', 'answer' => '7', 'explanation' => '12 - 5 = 7.'],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Multiplicación',
                        'description' => 'Operación de multiplicar números enteros.',
                        'materials' => [
                            ['type' => 'text', 'title' => 'Introducción a la multiplicación', 'content' => 'Multiplicar es sumar un número varias veces...'],
                        ],
                    ],
                    [
                        'title' => 'División',
                        'description' => 'Repartir una cantidad en partes iguales.',
                        'materials' => [
                            ['type' => 'text', 'title' => 'Introducción a la división', 'content' => "La división reparte una cantidad en partes iguales. Por ejemplo, 12 ÷ 3 = 4 porque al repartir 12 elementos en 3 grupos, cada grupo recibe 4.\n\nLa división es la operación inversa de la multiplicación: si 4 × 3 = 12, entonces 12 ÷ 3 = 4."],
                        ],
                        'quiz' => [
                            'title' => 'Quiz: División',
                            'questions' => [
                                ['type' => 'single_choice', 'statement' => '¿Cuánto es 20 ÷ 4?', 'options' => ['4', '*5', '6'], 'explanation' => '4 × 5 = 20, por eso 20 ÷ 4 = 5.'],
                                ['type' => 'short_answer', 'statement' => 'Escribe el resultado de 18 ÷ 3', 'answer' => '6', 'explanation' => '3 × 6 = 18, por eso 18 ÷ 3 = 6.'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Introducción a la Programación',
                'description' => 'Los conceptos que necesitas antes de escribir tu primer programa, sin importar el lenguaje.',
                'topics' => [
                    [
                        'title' => 'Qué es un algoritmo',
                        'description' => 'Pensar en pasos antes de programar.',
                        'materials' => [
                            ['type' => 'text', 'title' => '¿Qué es un algoritmo?', 'content' => "Un algoritmo es una secuencia ordenada y finita de pasos que permite resolver un problema. Una receta de cocina es un buen ejemplo: tiene ingredientes de entrada, pasos en orden y un resultado final.\n\nAntes de escribir código, un buen programador piensa en el algoritmo que va a seguir."],
                            ['type' => 'link', 'title' => 'Documentación para aprender más', 'content' => 'https://developer.mozilla.org/es/'],
                        ],
                    ],
                    [
                        'title' => 'Variables y tipos de datos',
                        'description' => 'Cómo guarda información un programa.',
                        'materials' => [
                            ['type' => 'text', 'title' => 'Variables', 'content' => "Una variable es un espacio con nombre donde un programa guarda un valor. Su contenido puede cambiar mientras el programa se ejecuta.\n\nLos tipos de datos más comunes son: números enteros, números decimales, texto y booleanos (verdadero o falso)."],
                        ],
                        'quiz' => [
                            'title' => 'Quiz: Variables',
                            'questions' => [
                                ['type' => 'single_choice', 'statement' => '¿Qué tipo de dato representa mejor un valor de verdadero o falso?', 'options' => ['*Booleano', 'Texto', 'Entero'], 'explanation' => 'Los booleanos solo pueden valer verdadero o falso.'],
                                ['type' => 'true_false', 'statement' => 'Una variable puede cambiar de valor mientras el programa se ejecuta.', 'options' => ['*Verdadero', 'Falso'], 'explanation' => 'Por eso se llaman variables: su valor puede variar.'],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Condicionales',
                        'description' => 'Tomar decisiones dentro de un programa.',
                        'materials' => [
                            ['type' => 'text', 'title' => 'Si ocurre esto, haz aquello', 'content' => "Un condicional ejecuta cierto código solo cuando se cumple una condición. La estructura más común es «si... entonces... si no...».\n\nEjemplo: si la edad es mayor o igual a 18, mostrar «Eres mayor de edad»; si no, mostrar «Eres menor de edad»."],
                        ],
                        'quiz' => [
                            'title' => 'Quiz: Condicionales',
                            'questions' => [
                                ['type' => 'single_choice', 'statement' => '¿Qué palabra clave usan muchos lenguajes para ejecutar código solo si se cumple una condición?', 'options' => ['*if', 'for', 'print'], 'explanation' => '«if» significa «si» y es la base de los condicionales.'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Inglés Básico',
                'description' => 'Primeras frases, saludos y vocabulario para empezar a comunicarte en inglés.',
                'topics' => [
                    [
                        'title' => 'Saludos y presentaciones',
                        'description' => 'Cómo saludar y presentarte.',
                        'materials' => [
                            ['type' => 'text', 'title' => 'Greetings', 'content' => "Saludos comunes: Hello / Hi (hola), Good morning (buenos días), Good afternoon (buenas tardes), Good evening (buenas noches al llegar), Goodbye (adiós).\n\nPara presentarte: «My name is Laura» (me llamo Laura) y «Nice to meet you» (mucho gusto)."],
                        ],
                        'quiz' => [
                            'title' => 'Quiz: Saludos',
                            'questions' => [
                                ['type' => 'single_choice', 'statement' => '¿Cómo se dice «Buenos días» en inglés?', 'options' => ['*Good morning', 'Good night', 'Goodbye'], 'explanation' => '«Good morning» se usa para saludar por la mañana.'],
                                ['type' => 'short_answer', 'statement' => 'Completa la frase con una palabra: My name ___ Laura.', 'answer' => 'is', 'explanation' => 'Con «name» se usa el verbo «is»: My name is Laura.'],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Números y colores',
                        'description' => 'Vocabulario esencial del 1 al 10 y los colores.',
                        'materials' => [
                            ['type' => 'text', 'title' => 'Numbers and colors', 'content' => "Números: one, two, three, four, five, six, seven, eight, nine, ten.\n\nColores: red (rojo), blue (azul), green (verde), yellow (amarillo), black (negro), white (blanco)."],
                        ],
                        'quiz' => [
                            'title' => 'Quiz: Números y colores',
                            'questions' => [
                                ['type' => 'multiple_choice', 'statement' => '¿Cuáles de estas palabras son colores en inglés?', 'options' => ['*Red', '*Blue', 'Seven', 'Table'], 'explanation' => 'Red y Blue son colores; Seven es un número y Table significa mesa.'],
                                ['type' => 'true_false', 'statement' => '«Three» significa «tres».', 'options' => ['*Verdadero', 'Falso'], 'explanation' => 'Three es el número 3.'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Ciencias Naturales',
                'description' => 'Un recorrido por los seres vivos y el lugar que ocupa la Tierra en el universo.',
                'topics' => [
                    [
                        'title' => 'La célula',
                        'description' => 'La unidad básica de la vida.',
                        'materials' => [
                            ['type' => 'text', 'title' => 'La unidad de la vida', 'content' => "La célula es la unidad básica de todos los seres vivos. Existen dos grandes tipos: las células procariotas, que no tienen un núcleo definido (como las bacterias), y las eucariotas, que sí lo tienen (como las de animales y plantas).\n\nEl núcleo guarda el material genético de la célula."],
                        ],
                        'quiz' => [
                            'title' => 'Quiz: La célula',
                            'questions' => [
                                ['type' => 'single_choice', 'statement' => '¿Qué estructura guarda el material genético en una célula eucariota?', 'options' => ['*El núcleo', 'La membrana', 'El citoplasma'], 'explanation' => 'El núcleo contiene el ADN de la célula eucariota.'],
                                ['type' => 'true_false', 'statement' => 'Todas las células tienen un núcleo definido.', 'options' => ['Verdadero', '*Falso'], 'explanation' => 'Las células procariotas, como las bacterias, no tienen núcleo definido.'],
                            ],
                        ],
                    ],
                    [
                        'title' => 'El sistema solar',
                        'description' => 'El Sol y los astros que giran a su alrededor.',
                        'materials' => [
                            ['type' => 'text', 'title' => 'Nuestro vecindario en el espacio', 'content' => "El sistema solar está formado por el Sol y los cuerpos que giran a su alrededor: ocho planetas, sus lunas, asteroides y cometas.\n\nLos planetas más cercanos al Sol (Mercurio, Venus, Tierra y Marte) son rocosos. Los más lejanos (Júpiter, Saturno, Urano y Neptuno) son gigantes de gas o de hielo."],
                        ],
                        'quiz' => [
                            'title' => 'Quiz: El sistema solar',
                            'questions' => [
                                ['type' => 'short_answer', 'statement' => '¿Cuántos planetas tiene el sistema solar? (escribe el número)', 'answer' => '8', 'explanation' => 'Son ocho: Mercurio, Venus, Tierra, Marte, Júpiter, Saturno, Urano y Neptuno.'],
                                ['type' => 'multiple_choice', 'statement' => '¿Cuáles de estos planetas son rocosos?', 'options' => ['*Mercurio', 'Júpiter', '*Tierra', 'Saturno'], 'explanation' => 'Mercurio y la Tierra son rocosos; Júpiter y Saturno son gigantes gaseosos.'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}