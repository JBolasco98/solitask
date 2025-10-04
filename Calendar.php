<?php
class Calendar {
  private $active_year, $active_month, $active_day;
  private $events = [];

  public function __construct($date = null) {
    $this->active_year = $date ? date('Y', strtotime($date)) : date('Y');
    $this->active_month = $date ? date('m', strtotime($date)) : date('m');
    $this->active_day = $date ? date('d', strtotime($date)) : date('d');
  }

  public function add_event($txt, $date, $days = 1, $color = '') {
    $color = $color ? ' ' . $color : '';
    $this->events[] = [$txt, $date, $days, $color];
  }

  public function __toString() {
    $num_days = date('t', strtotime("$this->active_year-$this->active_month-01"));
    $first_day_of_week = date('w', strtotime("$this->active_year-$this->active_month-01"));
    $html = '<div class="calendar">';
    $html .= '<div class="header"><div class="month-year">' . date('F Y', strtotime("$this->active_year-$this->active_month-01")) . '</div></div>';
    $html .= '<div class="days">';
    foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day) {
      $html .= '<div class="day_name">' . $day . '</div>';
    }
    for ($i = 0; $i < $first_day_of_week; $i++) {
      $html .= '<div class="day_num ignore"></div>';
    }
    for ($i = 1; $i <= $num_days; $i++) {
      $date = "$this->active_year-$this->active_month-" . str_pad($i, 2, '0', STR_PAD_LEFT);
      $selected = ($i == $this->active_day) ? ' selected' : '';
      $html .= '<div class="day_num' . $selected . '"><span>' . $i . '</span>';
      foreach ($this->events as $event) {
        $event_start = strtotime($event[1]);
        $event_end = strtotime("+".($event[2]-1)." days", $event_start);
        $current_day = strtotime($date);
        if ($current_day >= $event_start && $current_day <= $event_end) {
          $html .= '<div class="event' . $event[3] . '">' . $event[0] . '</div>';
        }
      }
      $html .= '</div>';
    }
    $html .= '</div></div>';
    return $html;
  }
}
?>
