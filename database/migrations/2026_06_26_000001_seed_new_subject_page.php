<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('pages')->where('slug', 'new-subject')->exists()) {
            return;
        }

        DB::table('pages')->insert([
            'title'    => '"Character Building" as an Independent Subject',
            'slug'     => 'new-subject',
            'template' => 'new-subject',
            'status'   => true,
            'meta'     => json_encode([
                'launching_heading' => "LAUNCHING \"CHARACTER EDUCATION\" AS A SEPARATE SUBJECT FOR MUSLIM INSTITUTIONS, ISLAMIC\nCOMMUNITY PROGRAMMES, HOME-SCHOOLING AND ONLINE-TEACHING",

                'para_1' => 'The programme has been introduced to convey ethics to children pedagogically and effectively. The programme aims at a unique project which is a comprehensive ethical programme that will grow and boost children\'s values while they grow up.',
                'para_2' => 'The curriculum aims to provide the student/pupil with a comprehensive ethical programme that will grow and boost children\'s values while they grow up. This is a programme which contains all the pedagogical ingredients that have so far not been put together with the aim of teaching character building within the Islamic tradition.',
                'para_3' => 'Character Building is a complementary subject taught alongside traditional courses. It is a subject which aims at developing students\'/pupils\' moral, emotional and social skills and competencies, which comprises different areas of skills like self-awareness, empathy, self-control, moral reasoning, self-reflection, problem-solving and behavioural self-regulation.',
                'para_4' => 'Almost no homework and no grading but lots of stories, interactive communication, playing, untraditional activities and exercises, all presented with a touch of entertainment, we hope this will become one of the children\'s favourite subjects.',

                'approach_text' => 'The approach of this Theme, which also is a turning point in the educational traditions of religious education for Muslim communities all over the world. All types of:',

                'bullet_items' => implode("\n", [
                    'Stories',
                    'Real events',
                    'Pictures',
                    'Creative and involving pedagogical activities and exercises through dynamic teaching',
                    'Training Forums of problem-solving and mediation',
                    'Methods of positive reinforcement (which is in line with the teaching of Islam)',
                    'Skill training techniques and strategies',
                    'Motivational self-reading',
                    'Humor and jokes with a point',
                    'Self-leading techniques and strategies',
                    'Self-motivated self-reading',
                    'Appropriately use of quotations and sayings',
                    'Delicately placed Quranic verses and Hadiths',
                    "Music (kept to a minimum), in an attempt to involve, motivate and develop the pupils' creativity",
                ]),

                'quote_text'           => '"What is the use of worldly education if our children do not develop into good human beings? This is a subject where the child\'s Muslim identity is strengthened from the roots but delicately and positively."',
                'educationists_text'   => 'Educationists have labelled this "as one of the first serious international attempts at Integrated Character Education for Muslims in modern times. A need which has been left unaddressed for too long but is now finally available."',
                'programme_aims_text'  => 'The programme aims to motivate and inspire teachers, educators and parents to convey ethics creatively, motivationally, pedagogically in ways that are constructive and have long-term effectiveness.',
                'closing_prayer_text'  => 'May this effort, by the grace of Allah, begin a trend of teaching ethics based on Islamic values and pedagogical teaching methods and may this subject contribute to reinforce the values of children in their practical lives.',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('pages')->where('slug', 'new-subject')->delete();
    }
};
