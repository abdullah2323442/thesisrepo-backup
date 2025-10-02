<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Report;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Supervisor;
use App\Models\AreaOfInterest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class HomeSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_displays_home_page_successfully()
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewIs('home');
        $response->assertViewHas(['reports', 'supervisors', 'availableYears', 'areasOfInterest', 'popularKeywords']);
    }

    /** @test */
    public function it_searches_reports_by_title()
    {
        $report = $this->createApprovedReport([
            'project_title' => 'Machine Learning in Healthcare'
        ]);

        $response = $this->get(route('home', ['search' => 'Machine Learning']));

        $response->assertStatus(200);
        $response->assertSee('Machine Learning in Healthcare');
    }

    /** @test */
    public function it_searches_reports_by_abstract()
    {
        $report = $this->createApprovedReport([
            'project_title' => 'AI Research',
            'abstract_md' => 'This thesis explores deep learning algorithms for image recognition.'
        ]);

        $response = $this->get(route('home', ['search' => 'deep learning']));

        $response->assertStatus(200);
        $response->assertSee('AI Research');
    }

    /** @test */
    public function it_searches_reports_by_keywords()
    {
        $report = $this->createApprovedReport([
            'project_title' => 'Neural Networks Study',
            'keywords' => json_encode(['artificial intelligence', 'neural networks', 'deep learning'])
        ]);

        $response = $this->get(route('home', ['search' => 'neural networks']));

        $response->assertStatus(200);
        $response->assertSee('Neural Networks Study');
    }

    /** @test */
    public function it_searches_reports_by_author_name()
    {
        $group = Group::factory()->create();
        GroupStudent::create([
            'group_id' => $group->id,
            'student_id' => '2023-CS-001',
            'student_name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $report = $this->createApprovedReport([
            'group_id' => $group->id,
            'project_title' => 'Research Project'
        ]);

        $response = $this->get(route('home', ['search' => 'John Doe']));

        $response->assertStatus(200);
        $response->assertSee('Research Project');
    }

    /** @test */
    public function it_searches_reports_by_student_id()
    {
        $group = Group::factory()->create();
        GroupStudent::create([
            'group_id' => $group->id,
            'student_id' => '2023-CS-001',
            'student_name' => 'Jane Smith',
            'email' => 'jane@example.com'
        ]);

        $report = $this->createApprovedReport([
            'group_id' => $group->id,
            'project_title' => 'Thesis Project'
        ]);

        $response = $this->get(route('home', ['search' => '2023-CS-001']));

        $response->assertStatus(200);
        $response->assertSee('Thesis Project');
    }

    /** @test */
    public function it_searches_reports_by_supervisor_name()
    {
        $supervisor = Supervisor::factory()->create([
            'fullname' => 'Dr. Sarah Johnson',
            'is_active' => true
        ]);

        $group = Group::factory()->create([
            'supervisor_id' => $supervisor->id
        ]);

        $report = $this->createApprovedReport([
            'group_id' => $group->id,
            'project_title' => 'Supervised Research'
        ]);

        $response = $this->get(route('home', ['search' => 'Sarah Johnson']));

        $response->assertStatus(200);
        $response->assertSee('Supervised Research');
    }

    /** @test */
    public function it_filters_reports_by_area_of_interest()
    {
        $area = AreaOfInterest::factory()->create([
            'name' => 'Artificial Intelligence',
            'is_active' => true
        ]);

        $report = $this->createApprovedReport([
            'area_of_interest_id' => $area->id,
            'project_title' => 'AI Research'
        ]);

        $otherReport = $this->createApprovedReport([
            'project_title' => 'Other Research'
        ]);

        $response = $this->get(route('home', ['area_of_interest' => $area->id]));

        $response->assertStatus(200);
        $response->assertSee('AI Research');
        $response->assertDontSee('Other Research');
    }

    /** @test */
    public function it_filters_reports_by_year()
    {
        $report2023 = $this->createApprovedReport([
            'project_title' => 'Research 2023',
            'approved_at' => '2023-06-15'
        ]);

        $report2024 = $this->createApprovedReport([
            'project_title' => 'Research 2024',
            'approved_at' => '2024-06-15'
        ]);

        $response = $this->get(route('home', ['year_from' => 2024]));

        $response->assertStatus(200);
        $response->assertSee('Research 2024');
        $response->assertDontSee('Research 2023');
    }

    /** @test */
    public function it_filters_reports_by_supervisor()
    {
        $supervisor1 = Supervisor::factory()->create(['is_active' => true]);
        $supervisor2 = Supervisor::factory()->create(['is_active' => true]);

        $group1 = Group::factory()->create(['supervisor_id' => $supervisor1->id]);
        $group2 = Group::factory()->create(['supervisor_id' => $supervisor2->id]);

        $report1 = $this->createApprovedReport([
            'group_id' => $group1->id,
            'project_title' => 'Report by Supervisor 1'
        ]);

        $report2 = $this->createApprovedReport([
            'group_id' => $group2->id,
            'project_title' => 'Report by Supervisor 2'
        ]);

        $response = $this->get(route('home', ['supervisor' => $supervisor1->id]));

        $response->assertStatus(200);
        $response->assertSee('Report by Supervisor 1');
        $response->assertDontSee('Report by Supervisor 2');
    }

    /** @test */
    public function it_filters_reports_by_comma_separated_keywords()
    {
        $report = $this->createApprovedReport([
            'project_title' => 'ML Research',
            'keywords' => json_encode(['machine learning', 'data science'])
        ]);

        $response = $this->get(route('home', ['keywords' => 'machine learning, data science']));

        $response->assertStatus(200);
        $response->assertSee('ML Research');
    }

    /** @test */
    public function it_sorts_reports_by_newest()
    {
        $oldReport = $this->createApprovedReport([
            'project_title' => 'Old Research',
            'approved_at' => '2023-01-01'
        ]);

        $newReport = $this->createApprovedReport([
            'project_title' => 'New Research',
            'approved_at' => '2024-01-01'
        ]);

        $response = $this->get(route('home', ['sort' => 'newest']));

        $response->assertStatus(200);
        // Check that new research appears before old research in the HTML
        $content = $response->getContent();
        $newPos = strpos($content, 'New Research');
        $oldPos = strpos($content, 'Old Research');
        $this->assertLessThan($oldPos, $newPos);
    }

    /** @test */
    public function it_sorts_reports_by_oldest()
    {
        $oldReport = $this->createApprovedReport([
            'project_title' => 'Old Research',
            'approved_at' => '2023-01-01'
        ]);

        $newReport = $this->createApprovedReport([
            'project_title' => 'New Research',
            'approved_at' => '2024-01-01'
        ]);

        $response = $this->get(route('home', ['sort' => 'oldest']));

        $response->assertStatus(200);
        // Check that old research appears before new research in the HTML
        $content = $response->getContent();
        $newPos = strpos($content, 'New Research');
        $oldPos = strpos($content, 'Old Research');
        $this->assertLessThan($newPos, $oldPos);
    }

    /** @test */
    public function it_sorts_reports_alphabetically()
    {
        $reportB = $this->createApprovedReport(['project_title' => 'Beta Research']);
        $reportA = $this->createApprovedReport(['project_title' => 'Alpha Research']);

        $response = $this->get(route('home', ['sort' => 'title']));

        $response->assertStatus(200);
        // Check that Alpha appears before Beta in the HTML
        $content = $response->getContent();
        $alphaPos = strpos($content, 'Alpha Research');
        $betaPos = strpos($content, 'Beta Research');
        $this->assertLessThan($betaPos, $alphaPos);
    }

    /** @test */
    public function it_validates_search_input()
    {
        $response = $this->get(route('home', ['search' => str_repeat('a', 300)]));

        $response->assertSessionHasErrors('search');
    }

    /** @test */
    public function it_validates_area_of_interest_exists()
    {
        $response = $this->get(route('home', ['area_of_interest' => 99999]));

        $response->assertSessionHasErrors('area_of_interest');
    }

    /** @test */
    public function it_validates_supervisor_exists()
    {
        $response = $this->get(route('home', ['supervisor' => 99999]));

        $response->assertSessionHasErrors('supervisor');
    }

    /** @test */
    public function it_validates_sort_option()
    {
        $response = $this->get(route('home', ['sort' => 'invalid']));

        $response->assertSessionHasErrors('sort');
    }

    /** @test */
    public function it_validates_year_range()
    {
        $response = $this->get(route('home', ['year_from' => 1800]));

        $response->assertSessionHasErrors('year_from');
    }

    /** @test */
    public function it_combines_multiple_filters()
    {
        $area = AreaOfInterest::factory()->create(['is_active' => true]);
        $supervisor = Supervisor::factory()->create(['is_active' => true]);
        $group = Group::factory()->create(['supervisor_id' => $supervisor->id]);

        $matchingReport = $this->createApprovedReport([
            'group_id' => $group->id,
            'area_of_interest_id' => $area->id,
            'project_title' => 'Matching Research',
            'approved_at' => '2024-01-01'
        ]);

        $nonMatchingReport = $this->createApprovedReport([
            'project_title' => 'Non-Matching Research',
            'approved_at' => '2023-01-01'
        ]);

        $response = $this->get(route('home', [
            'area_of_interest' => $area->id,
            'supervisor' => $supervisor->id,
            'year_from' => 2024
        ]));

        $response->assertStatus(200);
        $response->assertSee('Matching Research');
        $response->assertDontSee('Non-Matching Research');
    }

    /** @test */
    public function it_paginates_results()
    {
        // Create 15 reports (more than the 12 per page limit)
        for ($i = 1; $i <= 15; $i++) {
            $this->createApprovedReport([
                'project_title' => "Research Project {$i}"
            ]);
        }

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Research Project 1');
        $response->assertDontSee('Research Project 13'); // Should be on page 2
    }

    /** @test */
    public function it_preserves_query_string_in_pagination()
    {
        // Create 15 reports
        for ($i = 1; $i <= 15; $i++) {
            $this->createApprovedReport([
                'project_title' => "Research {$i}",
                'abstract_md' => 'machine learning'
            ]);
        }

        $response = $this->get(route('home', ['search' => 'machine']));

        $response->assertStatus(200);
        // Check that pagination links include the search parameter
        $response->assertSee('search=machine');
    }

    /** @test */
    public function it_only_shows_approved_final_reports()
    {
        $approvedFinal = $this->createApprovedReport([
            'project_title' => 'Approved Final Report'
        ]);

        $draftReport = Report::factory()->create([
            'type' => 'final',
            'status' => Report::STATUS_DRAFT,
            'project_title' => 'Draft Report'
        ]);

        $generalReport = Report::factory()->create([
            'type' => 'general',
            'status' => Report::STATUS_APPROVED,
            'project_title' => 'General Report',
            'approved_at' => now()
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Approved Final Report');
        $response->assertDontSee('Draft Report');
        $response->assertDontSee('General Report');
    }

    /** @test */
    public function it_caches_supervisors_list()
    {
        Supervisor::factory()->count(5)->create(['is_active' => true]);

        // First request - should cache
        $this->get(route('home'));
        $this->assertTrue(Cache::has('active_supervisors'));

        // Second request - should use cache
        $this->get(route('home'));
        $cached = Cache::get('active_supervisors');
        $this->assertCount(5, $cached);
    }

    /** @test */
    public function it_caches_available_years()
    {
        $this->createApprovedReport(['approved_at' => '2023-01-01']);
        $this->createApprovedReport(['approved_at' => '2024-01-01']);

        // First request - should cache
        $this->get(route('home'));
        $this->assertTrue(Cache::has('available_report_years'));

        // Check cached data
        $cached = Cache::get('available_report_years');
        $this->assertContains(2023, $cached->toArray());
        $this->assertContains(2024, $cached->toArray());
    }

    /** @test */
    public function it_caches_areas_of_interest()
    {
        AreaOfInterest::factory()->count(3)->create(['is_active' => true]);

        // First request - should cache
        $this->get(route('home'));
        $this->assertTrue(Cache::has('active_areas_of_interest'));

        // Check cached data
        $cached = Cache::get('active_areas_of_interest');
        $this->assertCount(3, $cached);
    }

    /** @test */
    public function it_caches_popular_keywords()
    {
        $this->createApprovedReport([
            'keywords' => json_encode(['machine learning', 'AI'])
        ]);

        // First request - should cache
        $this->get(route('home'));
        $this->assertTrue(Cache::has('popular_keywords'));

        // Check cached data
        $cached = Cache::get('popular_keywords');
        $this->assertIsArray($cached);
    }

    /**
     * Helper method to create an approved final report
     */
    protected function createApprovedReport(array $attributes = []): Report
    {
        $defaults = [
            'type' => 'final',
            'status' => Report::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => User::factory()->create()->id,
        ];

        if (!isset($attributes['group_id'])) {
            $defaults['group_id'] = Group::factory()->create()->id;
        }

        return Report::factory()->create(array_merge($defaults, $attributes));
    }
}
