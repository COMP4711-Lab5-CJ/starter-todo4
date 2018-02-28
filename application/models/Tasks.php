<?php

/**
 * Models for the data/tasks.csv
 */
class Tasks extends CSV_Model {

        public function __construct()
        {
                parent::__construct(APPPATH . '../data/tasks.csv', 'id');
        }

        /* get all the task based on their catergorizes
        */
        function getCategorizedTasks()
        {
            // extract the undone tasks
            foreach ($this->all() as $task)
            {
                if ($task->status != 2)
                    $undone[] = $task;
            }

            // substitute the category name, for sorting
            foreach ($undone as $task)
                $task->group = $this->app->group($task->group);

            // order them by category
            usort($undone, "Tasks::orderByCategory");

            // convert the array of task objects into an array of associative objects
            foreach ($undone as $task)
                $converted[] = (array) $task;

        return $converted;
        }

        // return -1, 0, or 1 of $a's category name is earlier, equal to, or later than $b's
        function orderByCategory($a, $b)
        {
          if ($a->group < $b->group)
            return -1;
          elseif ($a->group > $b->group)
            return 1;
          else
            return 0;
        }

        // provide form validation rules
        public function rules()
        {
            $config = array(
                ['field' => 'task', 'label' => 'TODO task', 'rules' => 'alpha_numeric_spaces|max_length[64]'],
                ['field' => 'priority', 'label' => 'Priority', 'rules' => 'integer|less_than[4]'],
                ['field' => 'size', 'label' => 'Task size', 'rules' => 'integer|less_than[4]'],
                ['field' => 'group', 'label' => 'Task group', 'rules' => 'integer|less_than[5]'],
            );
            return $config;
        }

        // Render the current DTO
        private function showit()
        {
            $this->load->helper('form');
            $task = $this->session->userdata('task');
            $this->data['id'] = $task->id;

            // if no errors, pass an empty message
            if ( ! isset($this->data['error']))
                $this->data['error'] = '';

            $fields = array(
                'ftask'      => form_label('Task description') . form_input('task', $task->task),
                'fpriority'  => form_label('Priority') . form_dropdown('priority', $this->app->priority(), $task->priority),
                'zsubmit'    => form_submit('submit', 'Update the TODO task'),
            );
            $this->data = array_merge($this->data, $fields);

            $this->data['pagebody'] = 'itemedit';
            $this->render();
        }

        // Initiate adding a new task
        public function add()
        {
            $task = $this->tasks->create();
            $this->session->set_userdata('task', $task);
            $this->showit();
        }

        // initiate editing of a task
        public function edit($id = null)
        {
            if ($id == null)
                redirect('/mtce');
            $task = $this->tasks->get($id);
            $this->session->set_userdata('task', $task);
            $this->showit();
        }
}
