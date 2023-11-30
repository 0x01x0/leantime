@props([
    'includeTitle' => true,
    'randomImage' => '',
    'totalTickets' => 0,
    'projectCount' => 0,
    'closedTicketsCount' => 0,
    'ticketsInGoals' => 0,
    'doneTodayCount' => 0,
    'totalTodayCount' => 0,
])

<div class="">

    <div style="padding:10px 0px">

        <div class="center">
        <span style="font-size:44px; color:var(--main-action-color);">
            @php
                $date = new DateTime();
                $date->setTimezone(new DateTimeZone($_SESSION['usersettings.timezone']));
                $date = $date->format(__("language.timeformat"));
            @endphp

            {{ $date }}
        </span><br />
            <span style="font-size:24px; color:var(--main-action-color);">
            Hi {{ $currentUser['firstname'] }}
        </span><br /><br />
        </div>

        <div class="tw-flex tw-gap-x-[10px]">

            <div class="bigNumberBox tw-flex-1 tw-flex-grow">
                <div class="bigNumberBoxInner">
                    <div class="bigNumberBoxNumber">⏱️ {{ $doneTodayCount }}/{{ $totalTodayCount }} </div>
                    <div class="bigNumberBoxText">Time boxed tasks completed</div>
                </div>
            </div>

                <div class="bigNumberBox tw-flex-1 tw-flex-grow">
                    <div class="bigNumberBoxInner">
                        <div class="bigNumberBoxNumber">🥳 {{ $closedTicketsCount }} </div>
                        <div class="bigNumberBoxText">Tasks completed (last 7 days)</div>
                    </div>
                </div>

                <div class="bigNumberBox tw-flex-1 tw-flex-grow ">

                    <div class="bigNumberBoxInner">
                        <div class="bigNumberBoxNumber">📥 {{ $totalTickets }} </div>
                        <div class="bigNumberBoxText">Total tasks left</div>
                    </div>
                </div>

                <div class="bigNumberBox tw-flex-1 tw-flex-grow">

                    <div class="bigNumberBoxInner">
                        <div class="bigNumberBoxNumber">🎯 {{ $ticketsInGoals }} </div>
                        <div class="bigNumberBoxText">Goals you are contributing to</div>
                    </div>
                </div>



        </div>
    </div>





    <?php /*

    <div class='pull-right' style='max-width:150px; padding:20px'>
        <div  style='width:100%' class='svgContainer'>
            {!! file_get_contents(ROOT . "/dist/images/svg/" . $randomImage) !!}
        </div>
    </div>

    <h1 class="articleHeadline tw-pb-m">
        Welcome <strong>{{ $currentUser['firstname'] }}</strong>
    </h1>

    <p>You have <strong>{{ $totalTickets }} To-Dos</strong> across <strong> {{ $projectCount  }} projects</strong> assigned to you.</p>

    @dispatchEvent('afterWelcomeMessage')
 */ ?>

    <div class="clear"></div>

</div>

@dispatchEvent('afterWelcomeMessageBox')
