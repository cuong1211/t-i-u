<?php

declare(strict_types=1);

namespace App\Presenters;

use Matex\Evaluator;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Nette\Application\UI\Form;

final class BisectionPresenter extends BasePresenter
{
    protected function f($expr, $x)
    {
        $evaluator = new Evaluator();
        $evaluator->variables = ['x' => $x];

        return $evaluator->execute($expr);
    }

    protected function tinhHeSoVang(&$A, &$B, $a, $b)
    {
        $p = (sqrt(5) - 1) / 2;
        $k = $b - $a;
        $A = $b - $p * $k;
        $B = $a + $p * $k;
    }

    protected function createComponentMathForm(): Form
    {
        $form = new BootstrapForm();
        $form->renderMode = RenderMode::SIDE_BY_SIDE_MODE;
        $form->addText('expr', 'Biểu thức:')
            ->setRequired();
        $form->addText('a', 'a =')
            ->setRequired();
        $form->addText('b', 'b =')
            ->setRequired();
        $form->addText('epsilon', 'Sai số:')
            ->setDefaultValue('10^-4');
        $form->onSuccess[] = [$this, 'formSucceeded'];
        $form->addSubmit('submit', 'Giải');

        return $form;
    }

    public function formSucceeded(Form $form, array $values): void
    {
        $values = array_values($values);

        [$expr, $a, $b] = $values;

        if ($a > $b) {
            $this->flashMessage('Dữ liệu không hợp lệ: (a < b)', 'danger');
            $this->redirect('Homepage:');

            return;
        }

        try {
            $epsilon = (new Evaluator())->execute($values[3]);
            $L = $b - $a;
            $data = [];
            while ($L > $epsilon) {
                $x1 = $a + $L / 4;
                $x2 = $b - $L / 4;

                $data[] = [$x1, $x2];

                if ($this->f($expr, $x1) < $this->f($expr, $x2)) {
                    $b = $x2;
                } elseif ($this->f($expr, $x1) > $this->f($expr, $x2)) {
                    $a = $x1;
                } else {
                    $a = $x1;
                    $b = $x2;
                }

                $L = $b - $a;
            }

            $this->template->chiadoi = $data;

            [, $a, $b] = $values;
            $A = $B = 0;
            $L = $b - $a;
            $data = [];

            while ($L > $epsilon) {
                $data[] = [$a, $b];
                $this->tinhHeSoVang($A, $B, $a, $b);

                if ($this->f($expr, $A) < $this->f($expr, $B)) {
                    $b = $B;
                } elseif ($this->f($expr, $A) > $this->f($expr, $B)) {
                    $a = $A;
                } else {
                    $a = $A;
                    $b = $B;
                }

                $L = $b - $a;
            }

            $x = round((float) $a, 4);
            $this->template->catvang = $data;
            $this->template->x = $x;
            $this->template->fmin = $this->f($expr, $x);
        } catch (\Exception $e) {
            $this->flashMessage('Dữ liệu đầu vào không hợp lệ.', 'danger');
            $this->redirect('Homepage:');
        }
    }

    public function renderDefault()
    {
    }
}
