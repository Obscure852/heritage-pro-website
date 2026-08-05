@php
    $roster = [
        ['name' => 'Nonofo Kokotetso', 'position' => 'Development Lead', 'discipline' => 'Full stack'],
        ['name' => 'Tumisang Solomon', 'position' => 'DevOps', 'discipline' => 'Infrastructure & reliability'],
        ['name' => 'Bikash Silvakumar', 'position' => 'Developer', 'discipline' => 'Full stack'],
        ['name' => 'Oratile Mogapi', 'position' => 'UI', 'discipline' => 'Interface & design'],
    ];
@endphp
<section id="team" class="hp-section hp-band">
    <div class="hp-team">
        <div>
            <p class="hp-eyebrow">X. Team</p>
            <h2 class="hp-h2 hp-h2--sm">The people who build Heritage Pro.</h2>
            <p class="hp-lead">A small engineering team, in-house. The person who builds a module is the person who supports it — there is no outsourced tier between you and the code.</p>
            <div class="hp-team__counts">
                <div class="hp-team__count"><strong>{{ count($roster) }}</strong><span>Engineers on staff</span></div>
                <div class="hp-team__count"><strong>In-house</strong><span>Product &amp; support</span></div>
            </div>
        </div>
        <table class="hp-roster">
            <thead>
                <tr>
                    <th scope="col"><span class="hp-visually-hidden">No.</span></th>
                    <th scope="col">Name</th>
                    <th scope="col">Position</th>
                    <th scope="col">Discipline</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roster as $member)
                    <tr>
                        <td class="hp-roster__index">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="hp-roster__name">{{ $member['name'] }}</td>
                        <td class="hp-roster__position">{{ $member['position'] }}</td>
                        <td class="hp-roster__discipline">{{ $member['discipline'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
