<?php
declare(strict_types = 1);

namespace App\Controller\Schedules;

use App\Controller\Helpers\Breadcrumbs;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Date;

class ByMonthController extends SchedulesBaseController
{
    public function __invoke(Service $service, string $date, Breadcrumbs $breadcrumbs)
    {
        if ($this->shouldRedirectToOverriddenUrl($service)) {
            return $this->cachedRedirect(
                $service->getNetwork()->getOption('pid_override_url'),
                $service->getNetwork()->getOption('pid_override_code'),
                3600
            );
        }

        if (!$this->isValidDate($date)) {
            throw $this->createNotFoundException('Invalid date supplied');
        }

        $this->setAtiContentLabels('schedule', 'schedule-month');
        $this->setContextAndPreloadBranding($service);
        $this->setAtiContentId((string) $service->getPid(), 'pips');

        $firstOfMonth = Date::createFromFormat('Y/m|', $date, ApplicationTime::getLocalTimeZone())->firstOfMonth();
        $viewData = ['first_of_month' => $firstOfMonth, 'service' => $service];
        $this->overridenDescription = "This is the monthly broadcast schedule for " . $service->getName();
        // If the service is not active at all over the month, then the status code should be 404, so
        // that search engines do not index thousands of empty pages
        if (!$this->serviceIsActiveDuringMonth($service, $firstOfMonth)) {
            $this->response()->setStatusCode(404);
        }

        $opts = ['pid' => $service->getPid()];
        $this->breadcrumbs = $breadcrumbs
            ->forRoute('Schedules', 'schedules_home')
            ->forRoute($service->getName(), 'schedules_by_day', $opts)
            ->forRoute($firstOfMonth->format('Y'), 'schedules_by_year', ['year' => $firstOfMonth->format('Y')] + $opts)
            ->forRoute($firstOfMonth->format('F'), 'schedules_by_month', ['date' => $firstOfMonth->format('Y/m')] + $opts)
            ->toArray();

        return $this->renderWithChrome('schedules/by_month.html.twig', $viewData);
    }

    private function serviceIsActiveDuringMonth(Service $service, Date $firstOfMonth): bool
    {
        return (!$service->getStartDate() || $service->getStartDate() <= $firstOfMonth->endOfMonth()) && (!$service->getEndDate() || $firstOfMonth < $service->getEndDate());
    }

    private function isValidDate(string $date): bool
    {
        // validate format
        if (!preg_match('#\d{4}/\d{2}#', $date)) {
            return false;
        }

        // validate content
        list($year, $month) = explode('/', $date);

        if ($month < 1 || $month > 12 || $year < ByYearController::MINIMUM_VALID_YEAR) {
            return false;
        }

        return true;
    }
}
