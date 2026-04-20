@extends('layouts.master')
@section('title')
    Scheduling Engine Documentation
@endsection
@section('css')
    <style>
        .doc-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .doc-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px 32px;
            border-radius: 3px 3px 0 0;
        }

        .doc-header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }

        .doc-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .doc-body {
            padding: 32px;
        }

        .doc-toc {
            background: #f8f9fa;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            padding: 20px 24px;
            margin-bottom: 32px;
        }

        .doc-toc h3 {
            font-size: 15px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 12px 0;
        }

        .doc-toc ol {
            margin: 0;
            padding-left: 20px;
        }

        .doc-toc li {
            margin-bottom: 6px;
        }

        .doc-toc a {
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
        }

        .doc-toc a:hover {
            text-decoration: underline;
        }

        .doc-section {
            margin-bottom: 40px;
        }

        .doc-section h2 {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            padding-bottom: 10px;
            border-bottom: 2px solid #4e73df;
            margin-bottom: 16px;
        }

        .doc-section h3 {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin: 20px 0 10px 0;
        }

        .doc-section p, .doc-section li {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.7;
        }

        .doc-section ul, .doc-section ol {
            padding-left: 20px;
            margin-bottom: 12px;
        }

        .doc-section li {
            margin-bottom: 4px;
        }

        .constraint-badge-hard {
            display: inline-block;
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .constraint-badge-soft {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .gene-diagram {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin: 16px 0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            color: #334155;
            overflow-x: auto;
        }

        .param-card {
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 16px;
        }

        .param-card .param-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 15px;
            margin-bottom: 4px;
        }

        .param-card .param-default {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .param-card .param-desc {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
        }

        .scenario-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .scenario-header {
            padding: 16px 20px;
            font-weight: 600;
            font-size: 16px;
            color: white;
        }

        .scenario-header.small-school {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .scenario-header.medium-school {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .scenario-header.large-school {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .scenario-body {
            padding: 20px;
        }

        .scenario-body .scenario-desc {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .scenario-stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .scenario-stat {
            background: #f8f9fa;
            border-radius: 4px;
            padding: 10px 12px;
            text-align: center;
        }

        .scenario-stat .stat-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .scenario-stat .stat-value {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-top: 2px;
        }

        .settings-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .settings-table th {
            background: #f1f5f9;
            padding: 8px 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e2e8f0;
        }

        .settings-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #f3f4f6;
            color: #4b5563;
        }

        .settings-table tr:hover {
            background: #f8f9fa;
        }

        .stagnation-flow {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin: 16px 0;
        }

        .stagnation-step {
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 13px;
            color: #4338ca;
            text-align: center;
            min-width: 120px;
        }

        .stagnation-arrow {
            color: #9ca3af;
            font-size: 18px;
        }

        .tip-box {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            border-radius: 0 3px 3px 0;
            padding: 12px 16px;
            margin: 12px 0;
        }

        .tip-box .tip-title {
            font-weight: 600;
            color: #065f46;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .tip-box .tip-content {
            color: #047857;
            font-size: 13px;
            line-height: 1.5;
        }

        .warning-box {
            background: #fff7ed;
            border-left: 4px solid #f59e0b;
            border-radius: 0 3px 3px 0;
            padding: 12px 16px;
            margin: 12px 0;
        }

        .warning-box .warning-title {
            font-weight: 600;
            color: #92400e;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .warning-box .warning-content {
            color: #b45309;
            font-size: 13px;
            line-height: 1.5;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .back-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .doc-body {
                padding: 20px;
            }

            .scenario-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .stagnation-flow {
                flex-direction: column;
            }

            .stagnation-arrow {
                transform: rotate(90deg);
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('timetable.index') }}">Timetable</a>
        @endslot
        @slot('title')
            Scheduling Documentation
        @endslot
    @endcomponent

    <div class="container-fluid">
        <a href="{{ route('timetable.generation.settings') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Advanced Settings
        </a>

        <div class="doc-container">
            <div class="doc-header">
                <h1><i class="fas fa-book me-2"></i>Scheduling Engine Documentation</h1>
                <p>A detailed breakdown of how automated timetable generation works, what every setting controls, and optimal configurations for your school.</p>
            </div>

            <div class="doc-body">
                {{-- Table of Contents --}}
                <div class="doc-toc">
                    <h3>Contents</h3>
                    <ol>
                        <li><a href="#overview">Overview</a></li>
                        <li><a href="#genes">What Are Genes?</a></li>
                        <li><a href="#algorithm">How the Genetic Algorithm Works</a></li>
                        <li><a href="#parameters">Every Parameter Explained</a></li>
                        <li><a href="#scenarios">School Size Scenarios</a></li>
                        <li><a href="#stagnation">Stagnation &mdash; What It Is and Why It Matters</a></li>
                        <li><a href="#tuning">Practical Tips for Tuning</a></li>
                    </ol>
                </div>

                {{-- 1. Overview --}}
                <div class="doc-section" id="overview">
                    <h2>1. Overview</h2>
                    <p>
                        The scheduling engine uses a <strong>Genetic Algorithm (GA)</strong> to automatically build a timetable.
                        It treats the timetable as a problem to be "evolved":
                    </p>
                    <ol>
                        <li>Start with many random timetables (a <strong>population</strong>).</li>
                        <li>Score each one: does it have double-bookings? Does it respect teacher preferences, room availability, subject spread rules?</li>
                        <li>Keep the best timetables, combine and tweak them, and repeat.</li>
                        <li>After hundreds or thousands of iterations (<strong>generations</strong>), the best timetable found is returned.</li>
                    </ol>

                    <h3>Constraint Types</h3>
                    <p>The system respects two categories of rules:</p>

                    <p><span class="constraint-badge-hard">HARD</span> constraints &mdash; must never be violated:</p>
                    <ul>
                        <li>A teacher cannot teach two classes at the same time</li>
                        <li>A class cannot be in two rooms at the same time</li>
                        <li>A venue cannot be used by two classes at the same time</li>
                        <li>An assistant teacher cannot be in two places at the same time</li>
                        <li>Double/triple periods must start at valid boundaries (not straddling a break)</li>
                        <li>Core lessons and elective lessons for the same grade must not overlap</li>
                    </ul>

                    <p><span class="constraint-badge-soft">SOFT</span> constraints &mdash; penalised but not fatal:</p>
                    <ul>
                        <li>Teacher availability preferences (avoid certain days/periods)</li>
                        <li>Subject spread (e.g. Maths should not appear 3 times on Monday)</li>
                        <li>Room requirements (Science in a lab, IT in a computer room)</li>
                        <li>Room capacity (don't put 45 students in a room that seats 30)</li>
                        <li>Consecutive teaching limits (don't give a teacher 6 periods in a row)</li>
                        <li>Subject pairing rules (e.g. Maths and Science should/shouldn't be on the same day)</li>
                        <li>Period restrictions (e.g. PE should not be in period 1)</li>
                    </ul>
                </div>

                {{-- 2. What Are Genes? --}}
                <div class="doc-section" id="genes">
                    <h2>2. What Are Genes?</h2>
                    <p>
                        In genetics, a gene is the smallest unit of information. The scheduling engine borrows this metaphor:
                    </p>
                    <p>
                        <strong>A gene is one teaching block that needs to be placed on the timetable.</strong>
                    </p>
                    <p>
                        Each gene represents a specific teacher teaching a specific subject to a specific class for a specific duration (number of consecutive periods).
                    </p>

                    <h3>Example</h3>
                    <p>If Mr. Moagi teaches Maths to Form 1A, and the block allocation says Form 1A Maths needs 3 single periods and 1 double period, that produces <strong>4 genes</strong>:</p>

                    <div class="gene-diagram">
Gene 1: Mr. Moagi / Maths / Form 1A / 1 period  (single)
Gene 2: Mr. Moagi / Maths / Form 1A / 1 period  (single)
Gene 3: Mr. Moagi / Maths / Form 1A / 1 period  (single)
Gene 4: Mr. Moagi / Maths / Form 1A / 2 periods (double)
                    </div>

                    <h3>Properties of a Gene</h3>
                    <ul>
                        <li><strong>klassSubjectId</strong> &mdash; which class-subject allocation it belongs to</li>
                        <li><strong>teacherId</strong> &mdash; which teacher is assigned</li>
                        <li><strong>klassId</strong> &mdash; which class (e.g. Form 1A)</li>
                        <li><strong>subjectId</strong> &mdash; which subject (e.g. Maths)</li>
                        <li><strong>duration</strong> &mdash; 1 (single), 2 (double), or 3 (triple)</li>
                        <li><strong>gradeId</strong> &mdash; which grade level</li>
                        <li><strong>dayOfCycle</strong> &mdash; which day it is placed on (0 = unassigned)</li>
                        <li><strong>startPeriod</strong> &mdash; which period it starts at (0 = unassigned)</li>
                        <li><strong>venueId</strong> &mdash; assigned room</li>
                        <li><strong>assistantTeacherId</strong> &mdash; second teacher if team-teaching</li>
                    </ul>

                    <h3>Key Terminology</h3>
                    <ul>
                        <li><strong>Chromosome</strong> &mdash; One complete timetable (the full collection of every gene with a day and period assigned)</li>
                        <li><strong>Population</strong> &mdash; A set of chromosomes (complete timetable candidates) being evolved</li>
                        <li><strong>Gene Count</strong> &mdash; The total number of genes across every class and subject, calculated from block allocations: <code>SUM(singles + doubles + triples)</code></li>
                    </ul>

                    <div class="tip-box">
                        <div class="tip-title">Gene Count = Problem Complexity</div>
                        <div class="tip-content">
                            100 genes = simple school, easy to solve &bull;
                            500 genes = medium school, moderate difficulty &bull;
                            1000+ genes = large school, needs careful parameter tuning
                        </div>
                    </div>
                </div>

                {{-- 3. How the GA Works --}}
                <div class="doc-section" id="algorithm">
                    <h2>3. How the Genetic Algorithm Works</h2>
                    <p>Step-by-step, here is what happens when you press <strong>"Generate"</strong>:</p>

                    <h3>Step 1: Initialisation</h3>
                    <p>
                        The system creates a population of random timetables. Each one is built using a constraint-aware heuristic:
                        the hardest-to-place genes (those with the fewest valid slots) are placed first, and each candidate slot is
                        scored for quality. This is much better than pure random placement.
                    </p>

                    <h3>Step 2: Fitness Evaluation</h3>
                    <p>
                        Each timetable (chromosome) receives a <strong>fitness score from 0.0 to 1.0</strong>:
                    </p>
                    <ul>
                        <li><strong>1.0</strong> = perfect, no violations whatsoever</li>
                        <li><strong>0.0</strong> = catastrophically bad</li>
                    </ul>
                    <p>Hard constraint violations reduce the score drastically. Soft constraint violations reduce it mildly.</p>

                    <h3>Step 3: Selection</h3>
                    <p>
                        Parents are chosen using <strong>tournament selection</strong>: pick a small random group from the population,
                        and the fittest one wins. Larger tournament sizes mean stronger selection pressure (the best timetables dominate
                        more quickly, but diversity drops).
                    </p>

                    <h3>Step 4: Crossover</h3>
                    <p>
                        Two parent timetables are combined to produce children. For each teaching block, the child randomly inherits
                        the day/period assignment from one parent or the other. This lets good placements from both parents mix together.
                    </p>

                    <h3>Step 5: Mutation</h3>
                    <p>
                        Random changes are applied. A small percentage of genes are moved to new day/period positions.
                        Conflicted genes (those currently causing violations) are prioritised for mutation &mdash; they are more likely
                        to be moved than well-placed genes.
                    </p>

                    <h3>Step 6: Repair</h3>
                    <p>
                        The elite timetables (the top few) undergo local repair: the system tries a number of targeted moves to fix
                        remaining hard violations. This is a "memetic" step that accelerates convergence.
                    </p>

                    <h3>Step 7: Replacement</h3>
                    <p>
                        The new generation replaces the old one. The top N timetables (the "elite") are always preserved unchanged,
                        ensuring the best solution found so far is never lost.
                    </p>

                    <h3>Step 8: Repeat</h3>
                    <p>Steps 2&ndash;7 repeat for up to <em>max_generations</em> iterations. The algorithm stops early if a perfect solution is found (fitness = 1.0) or the user cancels.</p>

                    <h3>Step 9: Post-Processing</h3>
                    <p>
                        After the main GA loop, a final venue conflict resolution pass reassigns rooms where double-bookings remain.
                        The final timetable is then saved to the database.
                    </p>
                </div>

                {{-- 4. Parameters --}}
                <div class="doc-section" id="parameters">
                    <h2>4. Every Parameter Explained</h2>
                    <p>The Advanced Settings page lets you control the genetic algorithm's behaviour. Here is every parameter:</p>

                    <div class="param-card">
                        <div class="param-name">Population Size</div>
                        <div class="param-default">Default: 100 &bull; Range: 10&ndash;500</div>
                        <div class="param-desc">
                            How many timetable candidates exist at once.
                            <strong>Higher</strong> = more diverse solutions explored, better chance of finding a good timetable, but each generation takes longer.
                            <strong>Lower</strong> = faster generations, less memory, but less diversity.
                            For large schools (500+ genes), use a <strong>smaller</strong> population (40&ndash;60) with more generations.
                        </div>
                    </div>

                    <div class="param-card">
                        <div class="param-name">Max Generations</div>
                        <div class="param-default">Default: 500 &bull; Range: 50&ndash;5000</div>
                        <div class="param-desc">
                            The maximum number of evolutionary cycles before stopping.
                            <strong>Higher</strong> = algorithm has more time to improve, better results for complex problems.
                            <strong>Lower</strong> = finishes faster but may return a suboptimal timetable.
                            Larger schools need more generations &mdash; 1000+ genes should use at least 1000&ndash;1500 generations.
                        </div>
                    </div>

                    <div class="param-card">
                        <div class="param-name">Stagnation Limit</div>
                        <div class="param-default">Default: 30 &bull; Range: 5&ndash;500</div>
                        <div class="param-desc">
                            Consecutive generations without improvement before the algorithm adapts by increasing the mutation rate.
                            <strong>Higher</strong> = more patient, good for large complex problems where progress is naturally slow.
                            <strong>Lower</strong> = reacts quickly to getting stuck, good for small problems. See Section 6 for details.
                        </div>
                    </div>

                    <div class="param-card">
                        <div class="param-name">Repair Moves</div>
                        <div class="param-default">Default: 6 &bull; Range: 1&ndash;30</div>
                        <div class="param-desc">
                            Targeted local repair attempts applied to elite timetables each generation. Each repair move tries to fix one hard violation by relocating a conflicted gene.
                            <strong>Higher</strong> = faster elimination of double-bookings, especially valuable for large schools.
                            <strong>Lower</strong> = faster per generation, but hard violations persist longer.
                        </div>
                    </div>

                    <div class="param-card">
                        <div class="param-name">Mutation Rate</div>
                        <div class="param-default">Default: 0.05 &bull; Range: 0.01&ndash;0.50</div>
                        <div class="param-desc">
                            Probability that each gene will be randomly repositioned each generation. 0.05 = ~5% of genes moved.
                            <strong>Higher</strong> = more exploration, breaks out of local optima, but can be destructive.
                            <strong>Lower</strong> = preserves good placements, fine-tunes existing solutions.
                            When stagnation occurs, this rate is automatically increased up to 0.3.
                        </div>
                    </div>

                    <div class="param-card">
                        <div class="param-name">Crossover Rate</div>
                        <div class="param-default">Default: 0.80 &bull; Range: 0.10&ndash;1.00</div>
                        <div class="param-desc">
                            Probability that two parents will be combined to produce children. Crossover is the main way the GA discovers
                            new solutions. Keep at <strong>0.7&ndash;0.9</strong> for best results.
                        </div>
                    </div>

                    <div class="param-card">
                        <div class="param-name">Tournament Size</div>
                        <div class="param-default">Default: 5 &bull; Range: 2&ndash;20</div>
                        <div class="param-desc">
                            How many random timetables compete in each selection round &mdash; the fittest wins.
                            <strong>Higher</strong> = stronger selection pressure, faster convergence, less diversity.
                            <strong>Lower</strong> = weaker pressure, maintains diversity longer, slower convergence.
                            Use 3&ndash;5 for small/medium schools, 5&ndash;7 for large schools.
                        </div>
                    </div>

                    <div class="param-card">
                        <div class="param-name">Elite Count</div>
                        <div class="param-default">Default: 2 &bull; Range: 1&ndash;20</div>
                        <div class="param-desc">
                            How many of the best timetables are preserved unchanged into the next generation (elitism).
                            Ensures the best solution found so far is never lost. Use 2&ndash;3 for most schools.
                        </div>
                    </div>
                </div>

                {{-- 5. Scenarios --}}
                <div class="doc-section" id="scenarios">
                    <h2>5. School Size Scenarios</h2>
                    <p>
                        Below are three real-world scenarios showing the recommended settings for different school sizes.
                        These include the teacher count, approximate gene count, optimal parameter values, and expected generation times.
                    </p>

                    {{-- Small School --}}
                    <div class="scenario-card">
                        <div class="scenario-header small-school">
                            <i class="fas fa-school me-2"></i> Scenario 1: Small School (&lt; 100 Teachers)
                        </div>
                        <div class="scenario-body">
                            <div class="scenario-desc">
                                <strong>Example:</strong> Heritage Junior Secondary School with 65 teachers, 18 classes across Forms 1&ndash;3,
                                a 6-day cycle with 7 periods per day. Subjects include core subjects (Maths, English, Science, etc.)
                                with some elective groupings for practical subjects.
                            </div>

                            <div class="scenario-stats">
                                <div class="scenario-stat">
                                    <div class="stat-label">Teachers</div>
                                    <div class="stat-value">65</div>
                                </div>
                                <div class="scenario-stat">
                                    <div class="stat-label">Classes</div>
                                    <div class="stat-value">18</div>
                                </div>
                                <div class="scenario-stat">
                                    <div class="stat-label">Gene Count</div>
                                    <div class="stat-value">~280</div>
                                </div>
                                <div class="scenario-stat">
                                    <div class="stat-label">Expected Time</div>
                                    <div class="stat-value">1&ndash;4 min</div>
                                </div>
                            </div>

                            <table class="settings-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Recommended Value</th>
                                        <th>Rationale</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Population Size</td>
                                        <td><strong>80</strong></td>
                                        <td>Compact search space &mdash; moderate population gives good diversity without waste</td>
                                    </tr>
                                    <tr>
                                        <td>Max Generations</td>
                                        <td><strong>400</strong></td>
                                        <td>Sufficient for ~280 genes; the algorithm usually converges well before 400</td>
                                    </tr>
                                    <tr>
                                        <td>Stagnation Limit</td>
                                        <td><strong>25</strong></td>
                                        <td>Progress should be steady &mdash; adapt quickly if stuck</td>
                                    </tr>
                                    <tr>
                                        <td>Repair Moves</td>
                                        <td><strong>4</strong></td>
                                        <td>Fewer potential conflicts; 4 moves per generation is enough</td>
                                    </tr>
                                    <tr>
                                        <td>Mutation Rate</td>
                                        <td><strong>0.05</strong></td>
                                        <td>Default rate &mdash; the problem is simple enough that standard mutation suffices</td>
                                    </tr>
                                    <tr>
                                        <td>Crossover Rate</td>
                                        <td><strong>0.80</strong></td>
                                        <td>Standard crossover rate for healthy recombination</td>
                                    </tr>
                                    <tr>
                                        <td>Tournament Size</td>
                                        <td><strong>5</strong></td>
                                        <td>Balanced selection pressure for a small-to-medium problem</td>
                                    </tr>
                                    <tr>
                                        <td>Elite Count</td>
                                        <td><strong>2</strong></td>
                                        <td>Protect the top 2 solutions; more would reduce exploration</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="tip-box">
                                <div class="tip-title">Tip</div>
                                <div class="tip-content">
                                    For this school size, the "Small School" or "Medium School" profile will both work well.
                                    Most timetables should generate with zero or very few hard violations on the first attempt.
                                    If there are shared teachers across many classes, try the Medium profile for extra safety.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Medium School --}}
                    <div class="scenario-card">
                        <div class="scenario-header medium-school">
                            <i class="fas fa-building me-2"></i> Scenario 2: Medium School (100&ndash;120 Teachers)
                        </div>
                        <div class="scenario-body">
                            <div class="scenario-desc">
                                <strong>Example:</strong> A combined Junior &amp; Senior Secondary School with 110 teachers,
                                32 classes across Forms 1&ndash;5, a 6-day cycle with 7 periods per day. Multiple elective coupling groups
                                in the senior forms, shared teachers between junior and senior sections, and 4 specialist venues (2 labs, 1 computer room, 1 workshop).
                            </div>

                            <div class="scenario-stats">
                                <div class="scenario-stat">
                                    <div class="stat-label">Teachers</div>
                                    <div class="stat-value">110</div>
                                </div>
                                <div class="scenario-stat">
                                    <div class="stat-label">Classes</div>
                                    <div class="stat-value">32</div>
                                </div>
                                <div class="scenario-stat">
                                    <div class="stat-label">Gene Count</div>
                                    <div class="stat-value">~580</div>
                                </div>
                                <div class="scenario-stat">
                                    <div class="stat-label">Expected Time</div>
                                    <div class="stat-value">8&ndash;18 min</div>
                                </div>
                            </div>

                            <table class="settings-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Recommended Value</th>
                                        <th>Rationale</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Population Size</td>
                                        <td><strong>60</strong></td>
                                        <td>Reduced from default &mdash; each evaluation is expensive with 580 genes</td>
                                    </tr>
                                    <tr>
                                        <td>Max Generations</td>
                                        <td><strong>1000</strong></td>
                                        <td>Doubled from default; shared teachers and coupling groups need more iterations</td>
                                    </tr>
                                    <tr>
                                        <td>Stagnation Limit</td>
                                        <td><strong>60</strong></td>
                                        <td>Be patient &mdash; progress at this scale is naturally slow between breakthroughs</td>
                                    </tr>
                                    <tr>
                                        <td>Repair Moves</td>
                                        <td><strong>8</strong></td>
                                        <td>More potential conflicts from shared teachers; extra repair moves help resolve them</td>
                                    </tr>
                                    <tr>
                                        <td>Mutation Rate</td>
                                        <td><strong>0.08</strong></td>
                                        <td>Slightly higher &mdash; the rougher fitness landscape needs more exploration</td>
                                    </tr>
                                    <tr>
                                        <td>Crossover Rate</td>
                                        <td><strong>0.80</strong></td>
                                        <td>Standard rate; crossover remains the primary discovery mechanism</td>
                                    </tr>
                                    <tr>
                                        <td>Tournament Size</td>
                                        <td><strong>5</strong></td>
                                        <td>Balanced; maintains diversity while keeping selection pressure</td>
                                    </tr>
                                    <tr>
                                        <td>Elite Count</td>
                                        <td><strong>3</strong></td>
                                        <td>Protect 3 elites; at this scale, losing a top solution is costly</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="warning-box">
                                <div class="warning-title">Important Note</div>
                                <div class="warning-content">
                                    Schools with 100&ndash;120 teachers and shared teachers across sections will fall into the "Large School" profile,
                                    even though the teacher count might seem moderate. The gene count (driven by class count and block allocations)
                                    is the true measure of complexity, not the teacher count alone.
                                    If your gene count is above 500, use these settings rather than the Medium profile.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Large School --}}
                    <div class="scenario-card">
                        <div class="scenario-header large-school">
                            <i class="fas fa-city me-2"></i> Scenario 3: Large School (120+ Teachers)
                        </div>
                        <div class="scenario-body">
                            <div class="scenario-desc">
                                <strong>Example:</strong> A large multi-stream Senior Secondary School with 160 teachers,
                                48 classes across Forms 1&ndash;5 (some grades with 10+ streams), a 6-day cycle with 8 periods per day.
                                Heavy use of coupling groups for elective subjects, 8+ specialist venues, multiple team-teaching assignments,
                                and complex teacher availability constraints (part-time teachers, shared staff with neighbouring schools).
                            </div>

                            <div class="scenario-stats">
                                <div class="scenario-stat">
                                    <div class="stat-label">Teachers</div>
                                    <div class="stat-value">160</div>
                                </div>
                                <div class="scenario-stat">
                                    <div class="stat-label">Classes</div>
                                    <div class="stat-value">48</div>
                                </div>
                                <div class="scenario-stat">
                                    <div class="stat-label">Gene Count</div>
                                    <div class="stat-value">~1200</div>
                                </div>
                                <div class="scenario-stat">
                                    <div class="stat-label">Expected Time</div>
                                    <div class="stat-value">25&ndash;60 min</div>
                                </div>
                            </div>

                            <table class="settings-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Recommended Value</th>
                                        <th>Rationale</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Population Size</td>
                                        <td><strong>40</strong></td>
                                        <td>Minimal &mdash; each evaluation with 1200 genes is very expensive; fewer candidates per generation</td>
                                    </tr>
                                    <tr>
                                        <td>Max Generations</td>
                                        <td><strong>1500</strong></td>
                                        <td>Maximum recommended; the algorithm needs many cycles to navigate the vast search space</td>
                                    </tr>
                                    <tr>
                                        <td>Stagnation Limit</td>
                                        <td><strong>80</strong></td>
                                        <td>Very patient &mdash; at this scale, going 50+ generations without improvement is normal</td>
                                    </tr>
                                    <tr>
                                        <td>Repair Moves</td>
                                        <td><strong>10</strong></td>
                                        <td>Maximum repair effort; many potential conflicts from shared teachers and limited venues</td>
                                    </tr>
                                    <tr>
                                        <td>Mutation Rate</td>
                                        <td><strong>0.10</strong></td>
                                        <td>Double the default &mdash; aggressive exploration needed for this problem size</td>
                                    </tr>
                                    <tr>
                                        <td>Crossover Rate</td>
                                        <td><strong>0.80</strong></td>
                                        <td>Standard rate; still the primary recombination mechanism</td>
                                    </tr>
                                    <tr>
                                        <td>Tournament Size</td>
                                        <td><strong>5</strong></td>
                                        <td>Moderate pressure; with only 40 in the population, higher pressure risks premature convergence</td>
                                    </tr>
                                    <tr>
                                        <td>Elite Count</td>
                                        <td><strong>2</strong></td>
                                        <td>Minimal elitism; with a small population, reserving too many elites limits exploration</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="tip-box">
                                <div class="tip-title">Tip for Very Large Schools</div>
                                <div class="tip-content">
                                    If the first generation attempt still has hard violations, do not be alarmed &mdash; run generation again.
                                    The algorithm uses randomness, so each run explores different parts of the search space.
                                    A second or third run often produces better results. You can also try increasing max_generations to 2000
                                    or repair_moves to 15 if time permits.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Summary comparison table --}}
                    <h3 style="margin-top: 32px;">Quick Comparison</h3>
                    <div style="overflow-x: auto;">
                        <table class="settings-table">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th style="text-align:center;">Small (&lt;100)</th>
                                    <th style="text-align:center;">Medium (100&ndash;120)</th>
                                    <th style="text-align:center;">Large (120+)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Teachers</td>
                                    <td style="text-align:center;">~65</td>
                                    <td style="text-align:center;">~110</td>
                                    <td style="text-align:center;">~160</td>
                                </tr>
                                <tr>
                                    <td>Gene Count</td>
                                    <td style="text-align:center;">~280</td>
                                    <td style="text-align:center;">~580</td>
                                    <td style="text-align:center;">~1200</td>
                                </tr>
                                <tr>
                                    <td>Population Size</td>
                                    <td style="text-align:center;"><strong>80</strong></td>
                                    <td style="text-align:center;"><strong>60</strong></td>
                                    <td style="text-align:center;"><strong>40</strong></td>
                                </tr>
                                <tr>
                                    <td>Max Generations</td>
                                    <td style="text-align:center;"><strong>400</strong></td>
                                    <td style="text-align:center;"><strong>1000</strong></td>
                                    <td style="text-align:center;"><strong>1500</strong></td>
                                </tr>
                                <tr>
                                    <td>Stagnation Limit</td>
                                    <td style="text-align:center;"><strong>25</strong></td>
                                    <td style="text-align:center;"><strong>60</strong></td>
                                    <td style="text-align:center;"><strong>80</strong></td>
                                </tr>
                                <tr>
                                    <td>Repair Moves</td>
                                    <td style="text-align:center;"><strong>4</strong></td>
                                    <td style="text-align:center;"><strong>8</strong></td>
                                    <td style="text-align:center;"><strong>10</strong></td>
                                </tr>
                                <tr>
                                    <td>Mutation Rate</td>
                                    <td style="text-align:center;"><strong>0.05</strong></td>
                                    <td style="text-align:center;"><strong>0.08</strong></td>
                                    <td style="text-align:center;"><strong>0.10</strong></td>
                                </tr>
                                <tr>
                                    <td>Elite Count</td>
                                    <td style="text-align:center;"><strong>2</strong></td>
                                    <td style="text-align:center;"><strong>3</strong></td>
                                    <td style="text-align:center;"><strong>2</strong></td>
                                </tr>
                                <tr>
                                    <td>Expected Time</td>
                                    <td style="text-align:center;">1&ndash;4 min</td>
                                    <td style="text-align:center;">8&ndash;18 min</td>
                                    <td style="text-align:center;">25&ndash;60 min</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 6. Stagnation --}}
                <div class="doc-section" id="stagnation">
                    <h2>6. Stagnation &mdash; What It Is and Why It Matters</h2>

                    <h3>What Is Stagnation?</h3>
                    <p>
                        Stagnation occurs when the best fitness score in the population has not improved for a consecutive number
                        of generations. For example, if the stagnation limit is 30 and the best timetable's fitness has been 0.847
                        for 30 generations in a row, the algorithm is considered "stagnant".
                    </p>

                    <h3>Why Does It Happen?</h3>
                    <ol>
                        <li>
                            <strong>Premature Convergence</strong> &mdash; All timetables in the population become very similar.
                            Crossover produces children nearly identical to their parents, and small mutations are not enough to
                            discover better arrangements.
                        </li>
                        <li>
                            <strong>Local Optima</strong> &mdash; The algorithm has found a timetable that is good but not perfect.
                            Any small change makes it worse. To fix the remaining issues, you would need to move 5+ lessons
                            simultaneously, but the algorithm only moves 1&ndash;2 at a time.
                        </li>
                        <li>
                            <strong>Hard Constraint Density</strong> &mdash; When many constraints interact (shared teachers, limited
                            venues, coupling groups), there may be very few valid configurations.
                        </li>
                    </ol>

                    <h3>What Happens When Stagnation Is Detected?</h3>
                    <p>The adaptive mutation mechanism kicks in:</p>

                    <div class="stagnation-flow">
                        <div class="stagnation-step">
                            Mutation Rate<br><strong>0.05</strong><br>(normal)
                        </div>
                        <div class="stagnation-arrow"><i class="fas fa-arrow-right"></i></div>
                        <div class="stagnation-step">
                            30 gens, no improvement<br><strong>&rarr; 0.10</strong><br>(doubled)
                        </div>
                        <div class="stagnation-arrow"><i class="fas fa-arrow-right"></i></div>
                        <div class="stagnation-step">
                            30 more, still stuck<br><strong>&rarr; 0.20</strong><br>(doubled again)
                        </div>
                        <div class="stagnation-arrow"><i class="fas fa-arrow-right"></i></div>
                        <div class="stagnation-step">
                            30 more, still stuck<br><strong>&rarr; 0.30</strong><br>(max cap)
                        </div>
                    </div>

                    <p>
                        The moment a better timetable is found, the mutation rate <strong>resets back to its original value</strong> (e.g. 0.05).
                        This mechanism is crucial for large schools where the fitness landscape is complex.
                    </p>

                    <div class="tip-box">
                        <div class="tip-title">Key Insight</div>
                        <div class="tip-content">
                            A higher stagnation limit = more patience before adapting. For large schools, set it to 60&ndash;80
                            because progress between breakthroughs naturally takes longer. For small schools, 20&ndash;30 is fine
                            since the algorithm should find improvements steadily.
                        </div>
                    </div>
                </div>

                {{-- 7. Tuning Tips --}}
                <div class="doc-section" id="tuning">
                    <h2>7. Practical Tips for Tuning</h2>

                    <div class="tip-box">
                        <div class="tip-title">For Most Schools</div>
                        <div class="tip-content">
                            Just use the recommended profile. The system analyses your block allocations and suggests the best
                            profile automatically. No manual tuning needed.
                        </div>
                    </div>

                    <h3>If generation takes too long:</h3>
                    <ul>
                        <li>Reduce population size (e.g. from 100 to 60)</li>
                        <li>Reduce max generations</li>
                        <li>These trade quality for speed</li>
                    </ul>

                    <h3>If the result has many violations:</h3>
                    <ul>
                        <li>Increase max generations (give the algorithm more time)</li>
                        <li>Increase repair moves (more targeted fixing per generation)</li>
                        <li>Increase mutation rate slightly (e.g. 0.05 to 0.08) for more exploration</li>
                    </ul>

                    <h3>If the result is decent but not quite right:</h3>
                    <ul>
                        <li>Increase stagnation limit (be more patient)</li>
                        <li>Keep mutation rate low (preserve good placements)</li>
                        <li>Increase elite count to 3&ndash;4 (protect more top solutions)</li>
                        <li>Run generation again &mdash; each run may produce a different result due to randomness</li>
                    </ul>

                    <h3>If you have a very constrained timetable:</h3>
                    <p>(Many shared teachers, limited rooms, many coupling groups)</p>
                    <ul>
                        <li>Use the "Large School" or "Very Large School" profile even if your gene count is lower</li>
                        <li>The extra generations and higher mutation rate help navigate the dense constraint landscape</li>
                    </ul>

                    <div class="warning-box">
                        <div class="warning-title">General Rules</div>
                        <div class="warning-content">
                            <strong>Larger population + fewer generations</strong> = broader but shallower search.<br>
                            <strong>Smaller population + more generations</strong> = narrower but deeper search.<br>
                            For large schools, prefer the latter (deep search). For small schools, either approach works.<br>
                            When in doubt, <strong>increase max_generations</strong> &mdash; it is the safest way to improve results at the cost of time.
                        </div>
                    </div>
                </div>

                {{-- Back link at bottom --}}
                <div style="padding-top: 24px; border-top: 1px solid #e5e7eb; margin-top: 40px;">
                    <a href="{{ route('timetable.generation.settings') }}" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to Advanced Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
