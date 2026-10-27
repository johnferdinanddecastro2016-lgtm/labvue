<?php

namespace Tests\Feature;

use Tests\TestCase;

class StudentGradebookTest extends TestCase
{
    public function test_the_student_gradebook_is_served_from_the_homepage(): void
    {
        $this->withoutVite()
            ->get('/')
            ->assertOk()
            ->assertSee('id="app"', false)
            ->assertSee('Student gradebook');
    }
}
