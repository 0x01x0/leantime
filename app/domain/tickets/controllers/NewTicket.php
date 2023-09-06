<?php

namespace Leantime\Domain\Tickets\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Files\Services\Files as FileService;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Users\Services\Users as UserService;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Auth\Services\Auth;

    class NewTicket extends Controller
    {
        private ProjectService $projectService;
        private TicketService $ticketService;
        private SprintService $sprintService;
        private FileService $fileService;
        private CommentService $commentService;
        private TimesheetService $timesheetService;
        private UserService $userService;

        public function init(
            ProjectService $projectService,
            TicketService $ticketService,
            SprintService $sprintService,
            FileService $fileService,
            CommentService $commentService,
            TimesheetService $timesheetService,
            UserService $userService
        ) {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            $this->projectService = $projectService;
            $this->ticketService = $ticketService;
            $this->sprintService = $sprintService;
            $this->fileService = $fileService;
            $this->commentService = $commentService;
            $this->timesheetService = $timesheetService;
            $this->userService = $userService;

            if (!isset($_SESSION['lastPage'])) {
                $_SESSION['lastPage'] = BASE_URL . "/tickets/showKanban/";
            }
        }


        public function get()
        {
            $ticket = app()->make(TicketModel::class, [
                [
                    "userLastname" => $_SESSION['userdata']["name"],
                    "status" => 3,
                    "projectId" => $_SESSION['currentProject'],
                    "sprint" => $_SESSION['currentSprint'] ?? '',
                ],
            ]);

            $ticket->date =  $this->language->getFormattedDateString(date("Y-m-d H:i:s"));

            $this->tpl->assign('ticket', $ticket);
            $this->tpl->assign('ticketParents', $this->ticketService->getAllPossibleParents($ticket));
            $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
            $this->tpl->assign('ticketTypes', $this->ticketService->getTicketTypes());
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());

            $allProjectMilestones = $this->ticketService->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => $_SESSION["currentProject"]]);
            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->assign('sprints', $this->sprintService->getAllSprints($_SESSION["currentProject"]));

            $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
            $this->tpl->assign('ticketHours', 0);
            $this->tpl->assign('userHours', 0);

            $this->tpl->assign('timesheetsAllHours', 0);
            $this->tpl->assign('remainingHours', 0);

            $this->tpl->assign('userInfo', $this->userService->getUser($_SESSION['userdata']['id']));
            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($_SESSION["currentProject"]));

            $this->tpl->displayPartial('tickets.newTicketModal');
        }

        public function post($params)
        {

            if (isset($params['saveTicket']) || isset($params['saveAndCloseTicket'])) {
                $result = $this->ticketService->addTicket($params);

                if (is_array($result) === false) {
                    $this->tpl->setNotification($this->language->__("notifications.ticket_saved"), "success");

                    if (isset($params["saveAndCloseTicket"]) === true && $params["saveAndCloseTicket"] == 1) {
                        $this->tpl->redirect(BASE_URL . "/tickets/showTicket/" . $result . "?closeModal=1");
                    } else {
                        $this->tpl->redirect(BASE_URL . "/tickets/showTicket/" . $result);
                    }
                } else {
                    $this->tpl->setNotification($this->language->__($result["msg"]), "error");

                    $ticket = app()->make(TicketModel::class, [$params]);
                    $ticket->userLastname = $_SESSION['userdata']["name"];

                    $this->tpl->assign('ticket', $ticket);
                    $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
                    $this->tpl->assign('ticketTypes', $this->ticketService->getTicketTypes());
                    $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
                    $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());
                    $this->tpl->assign('milestones', $this->ticketService->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => $_SESSION["currentProject"]]));
                    $this->tpl->assign('sprints', $this->sprintService->getAllSprints($_SESSION["currentProject"]));

                    $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
                    $this->tpl->assign('ticketHours', 0);
                    $this->tpl->assign('userHours', 0);

                    $this->tpl->assign('timesheetsAllHours', 0);
                    $this->tpl->assign('remainingHours', 0);

                    $this->tpl->assign('userInfo', $this->userService->getUser($_SESSION['userdata']['id']));
                    $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($_SESSION["currentProject"]));

                    $this->tpl->displayPartial('tickets.newTicketModal');
                }
            }
        }
    }

}
