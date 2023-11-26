@props([
    'includeTitle' => true,
    'tickets' => [],
    'onTheClock' => false,
    'groupBy' => '',
    'allProjects' => [],
])

<div class=""
     id="myToDoBox"
     hx-get="{{BASE_URL}}/widgets/myToDos/get"
     hx-trigger="ticketUpdate from:body"
     hx-swap="outerHTML"
    >
    <div class="row" id="yourToDoContainer">
        <div class="col-md-12">

            <div class="marginBottomMd">

                <form method="get">
                    <h5 class="subtitle">{{ __('headlines.your_todos') }}</h5>

                    <div class="btn-group viewDropDown right">
                        <button class="btn dropdown-toggle " type="button" data-toggle="dropdown">{!! __("links.group_by") !!}</button>
                        <ul class="dropdown-menu">
                            <li><span class="radio">
                                    <input type="radio" name="groupBy"
                                           @if($groupBy == "time") checked='checked' @endif
                                           value="time" id="groupByDate"
                                           hx-get="{{BASE_URL}}/widgets/myToDos/get"
                                           hx-trigger="click"
                                           hx-target="#myToDoBox"
                                           hx-swap="outerHTML"
                                           hx-indicator="#myToDoBox .htmx-indicator"
                                        />
                                    <label for="groupByDate">{!! __("label.dates") !!}</label></span></li>
                            <li><span class="radio">
                                    <input type="radio"
                                           name="groupBy"
                                           @if($groupBy == "project") checked='checked' @endif
                                           value="project" id="groupByProject"
                                           hx-get="{{BASE_URL}}/widgets/myToDos/get"
                                           hx-trigger="click"
                                           hx-target="#myToDoBox"
                                           hx-swap="outerHTML"
                                           hx-indicator="#myToDoBox .htmx-indicator"
                                    />
                                    <label for="groupByProject">{!! __("label.project") !!}</label></span></li>
                        </ul>
                    </div>
                    <div class="right">
                        <label class="inline">{{  __('label.show') }}</label>
                        <select name="projectFilter" onchange="form.submit();">
                            <option value="">{{ __('labels.all_projects') }}</option>
                            @foreach($allProjects as $project)
                                <option value="{{  $project['id'] }}"
                                        @if($projectFilter == $project['id'])
                                            selected='selected'
                                    @endif
                                >{{ $project['name'] }}</option>
                            @endforeach
                        </select>
                        &nbsp;
                    </div>
                    <div class="clearall"></div>
                </form>
            </div>

            <div class="htmx-indicator">
                <x-global::loadingText type="card" />
            </div>

            @if($tickets !== null && count($tickets) == 0)
            <div class='center'>
                <div  style='width:30%' class='svgContainer'>
                    {!! file_get_contents(ROOT . "/dist/images/svg/undraw_a_moment_to_relax_bbpa.svg") !!}
                </div>
                <br />
                <h4>{{ __("headlines.no_todos_this_week") }}</h4>
                {{ __("text.take_the_day_off") }}
                <a href='{{ BASE_URL }}/tickets/showAll'>{{ __("links.goto_backlog") }}</a><br/><br/>
            </div>
            @endif

            @foreach ($tickets as $ticketGroup)

                @php
                    //Get first duedate if exist
                    $ticketCreationDueDate = '';
                    if (isset($ticketGroup['tickets'][0]) && $ticketGroup['tickets'][0]['dateToFinish'] != "0000-00-00 00:00:00" && $ticketGroup['tickets'][0]['dateToFinish'] != "1969-12-31 00:00:00") {
                        //Use the first due date as the new due date
                        $ticketCreationDueDate = $ticketGroup['tickets'][0]['dateToFinish'];
                    }

                    $groupProjectId = $_SESSION['currentProject'];

                    if ($groupBy == 'project' && isset($ticketGroup['tickets'][0])) {
                        $groupProjectId = $ticketGroup['tickets'][0]['projectId'];
                    }

                @endphp

                <a class="anchor" id="accordion_anchor_{{ $loop->index }}"></a>

                <x-global::accordion id="ticketBox1-{{ $loop->index }}">
                    <x-slot name="title">
                        {{ __($ticketGroup["labelName"]) }} ({{ count($ticketGroup["tickets"]) }})
                        <a class="titleInsertLink" href="javascript:void(0)" onclick="insertQuickAddForm({{ $loop->index }}, {{ $groupProjectId }}, '{{ $ticketCreationDueDate }}')"><i class="fa fa-plus"></i> {{ __('links.add_todo_no_icon') }}</a>
                    </x-slot>
                    <x-slot name="content">
                        <ul class="sortableTicketList" >

                            @if (count($ticketGroup['tickets']) == 0)
                                <em>Nothing to see here. Move on.</em><br /><br />
                            @endif

                            @foreach ($ticketGroup['tickets'] as $row)

                                @if($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00")

                                    @php
                                        $date = __("text.anytime")
                                    @endphp

                                @else

                                    @php
                                        $date = new DateTime($row['dateToFinish']);
                                        $date = $date->format(__("language.dateformat"));
                                    @endphp

                                @endif

                                <li class="ui-state-default" id="ticket_{{ $row['id'] }}" >
                                    <div class="ticketBox fixed priority-border-{{ $row['priority'] }}" data-val="{{ $row['id'] }}">
                                        <div class="row">
                                            <div class="col-md-12 timerContainer" style="padding:5px 15px;" id="timerContainer-{{ $row['id'] }}">

                                                @include("tickets::partials.ticketsubmenu", ["ticket" => $row, "onTheClock" => $onTheClock])

                                                <small>{{ $row['projectName'] }}</small><br />
                                                @if($row['dependingTicketId'] > 0)
                                                    <a href="#/tickets/showTicket/{{ $row['dependingTicketId'] }}">{{ $row['parentHeadline'] }}</a> //
                                                @endif
                                                <strong><a href="#/tickets/showTicket/{{ $row['id'] }}" >{{ $row['headline'] }}</a></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4" style="padding:0 15px;">
                                                {{ __("label.due") }}<input type="text" title="{{ __("label.due") }}" value="{{ $date }}" class="duedates secretInput" data-id="{{ $row['id'] }}" name="date" />
                                            </div>
                                            <div class="col-md-8" style="padding-top:3px;">
                                                <div class="right">

                                                    <div class="dropdown ticketDropdown effortDropdown show">
                                                        <a class="dropdown-toggle f-left  label-default effort" href="javascript:void(0);" role="button" id="effortDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text">
                                                            @if ($row['storypoints'] != '' && $row['storypoints'] > 0)
                                                                {{ $efforts["" . $row['storypoints']] }}
                                                            @else
                                                                {{ __("label.story_points_unkown") }}
                                                            @endif
                                                        </span>
                                                            &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                        </a>
                                                        <ul class="dropdown-menu" aria-labelledby="effortDropdownMenuLink{{ $row['id'] }}">
                                                            <li class="nav-header border">{{ __("dropdown.how_big_todo") }}</li>
                                                            @foreach($efforts as $effortKey => $effortValue)
                                                                <li class='dropdown-item'>
                                                                    <a href='javascript:void(0);'
                                                                       data-value='{{ $row['id'] . "_" . $effortKey }}'
                                                                       id='ticketEffortChange{{ $row['id'] . $effortKey }}'>
                                                                        {{ $effortValue }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>


                                                    <div class="dropdown ticketDropdown milestoneDropdown colorized show">
                                                        <a style="background-color:{{ $row['milestoneColor'] }}"
                                                           class="dropdown-toggle f-left  label-default milestone"
                                                           href="javascript:void(0);"
                                                           role="button" id="milestoneDropdownMenuLink{{ $row['id'] }}"
                                                           data-toggle="dropdown"
                                                           aria-haspopup="true"
                                                           aria-expanded="false">
                                                            <span class="text">
                                                                @if($row['milestoneid'] != "" && $row['milestoneid'] != 0)
                                                                    {{ $row['milestoneHeadline'] }}
                                                                @else
                                                                    {{  __("label.no_milestone") }}
                                                                @endif
                                                            </span>
                                                            &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                        </a>
                                                        <ul class="dropdown-menu" aria-labelledby="milestoneDropdownMenuLink{{ $row['id'] }}">
                                                            <li class="nav-header border">{{ __("dropdown.choose_milestone") }}</li>
                                                            <li class='dropdown-item'>
                                                                <a style='background-color:#b0b0b0'
                                                                   href='javascript:void(0);'
                                                                   data-label="{{__("label.no_milestone") }}"
                                                                   data-value='{{ $row['id'] }}_0_#b0b0b0'>
                                                                    {{ __("label.no_milestone") }}
                                                                </a>
                                                            </li>
                                                            @if(isset($milestones[$row['projectId']]))
                                                                @foreach($milestones[$row['projectId']] as $milestone)
                                                                    @if(is_object($milestone))
                                                                    <li class='dropdown-item'>
                                                                        <a href='javascript:void(0);'
                                                                           data-label='{{ $milestone->headline }}'
                                                                           data-value='{{ $row['id'] }}_{{ $milestone->id }}_{{ $milestone->tags }}'
                                                                           id='ticketMilestoneChange{{ $row['id'] . $milestone->id }}'
                                                                           style='background-color:{{ $milestone->tags }}'>
                                                                            {{ $milestone->headline }}
                                                                        </a>
                                                                    </li>
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        </ul>
                                                    </div>

                                                    <div class="dropdown ticketDropdown statusDropdown colorized show">
                                                        <a class="dropdown-toggle f-left status {{ $statusLabels[$row['projectId']][$row['status']]["class"] }}" href="javascript:void(0);" role="button" id="statusDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text">
                                                            @if(isset($statusLabels[$row['projectId']][$row['status']]))
                                                                {{ $statusLabels[$row['projectId']][$row['status']]["name"] }}
                                                            @else
                                                                unknown
                                                            @endif
                                                        </span>
                                                            &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                        </a>
                                                        <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink{{ $row['id'] }}">
                                                            <li class="nav-header border">{{ __("dropdown.choose_status") }}</li>

                                                            @foreach ($statusLabels[$row['projectId']] as $key => $label)
                                                                <li class='dropdown-item'>
                                                                    <a href='javascript:void(0);'
                                                                       class='{{ $label["class"] }}'
                                                                       data-label='{{ $label["name"] }}'
                                                                       data-value='{{ $row['id'] }}_{{ $key }}_{{ $label["class"] }}'
                                                                       id='ticketStatusChange{{$row['id'] . $key }}'>
                                                                        {{  $label["name"] }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </x-slot>
                </x-global::accordion>
            @endforeach
        </div>
    </div>
</div>


<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')

    function insertQuickAddForm(index, projectId, duedate) {
        jQuery(".quickaddForm").remove();

        jQuery("#accordion_"+index+" ul").prepend('<li class="quickaddForm">'+
            '<div class="ticketBox" id="ticket_new_'+index+'" style="padding:18px;">'+
            '<form method="post" class="form-group" action="#accordion_anchor_'+index+'" hx-post="{{BASE_URL}}/widgets/myToDos/addTodo" >'+
            '<input name="headline" type="text" title="{{ __("label.headline") }}" style="width:100%" placeholder="{{ __("input.placeholders.what_are_you_working_on") }}" />'+
            '<input type="submit" value="{{ __("buttons.save") }}" name="quickadd"  />'+
            '<input type="hidden" name="dateToFinish" id="dateToFinish" value="'+duedate+'" />'+
            '<input type="hidden" name="status" value="3" />'+
            '<input type="hidden" name="projectId" value="'+projectId+'" />'+
            '<input type="hidden" name="sprint" value="{{ $_SESSION['currentSprint'] }}" />&nbsp;'+
            '<a href="javascript:void(0);" onclick="jQuery(\'#ticket_new_'+index+'\').toggle(\'fast\');">'+
            '{{ __("links.cancel") }}'+
            '</a>'+
            '</form></div></li>');

    }

    jQuery('.todaysDate').text(moment().format('LLLL'));

    jQuery(document).ready(function(){

        @if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor))
            leantime.dashboardController.prepareHiddenDueDate();
            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initMilestoneDropdown();
            leantime.ticketsController.initStatusDropdown();
            leantime.ticketsController.initDueDateTimePickers();
        @else
            leantime.authController.makeInputReadonly(".maincontentinner");
        @endif

    });

</script>


