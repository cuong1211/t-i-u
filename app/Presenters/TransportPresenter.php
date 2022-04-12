<?php

declare(strict_types=1);

namespace App\Presenters;

use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Nette\Application\UI\Form;

final class TransportPresenter extends BasePresenter {
    protected function createComponentMathForm(): Form
    {
        $form = new BootstrapForm();
        $form->renderMode = RenderMode::SIDE_BY_SIDE_MODE;
        $form->addText('thu', 'Lượng thu:')
            ->setRequired();
        $form->addText('phat', 'Lượng phát:')
            ->setRequired();
        $form->addTextArea('in', 'Ma trận hệ số:')
            ->setHtmlAttribute('rows', 5)
            ->setRequired();
        
        $form->addSubmit('submit', 'Kết quả');
        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

    public function formSucceeded(Form $form, array $values)
    {
        [$thu, $phat, $matrix] = array_values($values);
        $thu = explode(' ', $thu);
        $phat = explode(' ', $phat);
        $matrix = explode("\n", $matrix);
        $in = [];
        $errors = [];
        $that = $this;

        if (array_sum($thu) !== array_sum($phat)) {
            $errors[] = 'Bài toán chưa cần bằng thu phát';
        }

        if (count($matrix) !== count($phat)) {
            $errors[] = 'Số hàng không khớp với số lượng phát';
        }

        foreach ($matrix as $cols) {
            $col = explode(' ', $cols);
            if (count($col) === count($thu)) {
                $in[] = $col;
            } else {
                $errors[] = 'Số cột không khớp với lượng thu';
            }
        }

        if (count($errors) > 0) {
            array_map(static function ($error) use ($that) {
                $that->flashMessage($error, 'alert alert-danger');
            }, $errors);

            $this->redirect('this');

            return;
        }

        $result = array_fill(0, count($phat), array_fill(0, count($thu), 'x'));

        $this->template->in = $in;
        $this->template->result[] = [$thu, $phat, $result];

        $k = 0;
        $solver = static function () use ($in, &$thu, &$phat, &$result, $that, &$solver, &$k) {
            $min = pow(10, 4);
            $x = $y = 0;
            $hasMin = false;

            foreach($in as $row => $cols) {
                foreach($cols as $col => $value) {
                    if ($phat[$row] && $thu[$col] && $value < $min) {
                        $hasMin = true;
                        $min = $value;
                        $x = $row;
                        $y = $col;
                    }
                }
            }

            if ($hasMin) {
                $k++;
                $min = min([$phat[$x], $thu[$y]]);
                $result[$x][$y] = (int) $min;
                $phat[$x] -= $min;
                $thu[$y] -= $min;

                $that->template->result[] = [$thu, $phat, $result];

                return $solver();
            }
        };

        $solver();

        $loop = [];
        $findLoop = function ($x, $y, $list = []) use ($result, &$findLoop, &$loop) {
            $list[] = [$x, $y];
            $countLoop = count($loop);
            $countList = count($list);

            if ($countLoop > 0 && $countLoop < $countList) {
                unset($list);
                return;
            }

            if ($countList > 3) {
                [$xFirst, $yFirst] = reset($list);
                if (($x == $xFirst || $y == $yFirst)) {
                    if ($xFirst != $list[2][0] && $yFirst != $list[2][1]) {
                        $loop = $list;
                    }
                }
            }

            foreach($result as $row => $value) {
                if ($row != $x && $result[$row][$y] != 'x') {
                    if (!in_array([$row, $y], $list)) {
                        $findLoop($row, $y, $list);
                    }
                }
            }

            foreach($result[$x] as $col => $value) {
                if ($col != $y && $result[$x][$col] != 'x') {
                    if (!in_array([$x, $col], $list)) {
                        $findLoop($x, $col, $list);
                    }
                }
            }
        };

        if ($k < (count($thu) + count($phat) - 1)) {
            $min = pow(10, 4);
            foreach($in as $row => $cols) {
                 foreach($cols as $col => $value) {
                    if ($result[$row][$col] == 'x' && $value < $min) {
                        $findLoop($row, $col, $loop);
                        if (count($loop) == 0) {
                            $min = $value;
                            $x = $row;
                            $y = $col;
                        }
                        $loop = [];
                    }
                }
            }
            $result[$x][$y] = 0;
            $this->template->result[] = [$thu, $phat, $result];
            unset($x, $y, $min);
        }

        for ($i = 0; $i < 3; $i++) {
            $this->step2($in, $result);
        }

        $total = 0;
        foreach ($result as $row => $cols) {
            foreach ($cols as $col => $value) {
                if ($value != 'x') {
                    $total += $in[$row][$col]*$value;
                }
            }
        }
        $this->template->total = $total;
    }

    public function step2(&$in, &$result) {
            $loop = [];
            $findLoop = function ($x, $y, $list = []) use ($result, &$findLoop, &$loop) {
                $list[] = [$x, $y];
                $countLoop = count($loop);
                $countList = count($list);

                if ($countLoop > 0 && $countLoop < $countList) {
                    unset($list);
                    return;
                }

                if ($countList > 3) {
                    [$xFirst, $yFirst] = reset($list);
                    if (($x == $xFirst || $y == $yFirst)) {
                        if ($xFirst != $list[2][0] && $yFirst != $list[2][1]) {
                            $loop = $list;
                        }
                    }
                }

                foreach($result as $row => $value) {
                    if ($row != $x && $result[$row][$y] != 'x') {
                        if (!in_array([$row, $y], $list)) {
                            $findLoop($row, $y, $list);
                        }
                    }
                }

                foreach($result[$x] as $col => $value) {
                    if ($col != $y && $result[$x][$col] != 'x') {
                        if (!in_array([$x, $col], $list)) {
                            $findLoop($x, $col, $list);
                        }
                    }
                }
            };

            $uv = function () use ($in, $result) {
                $u = $v = [];
                $u[0] = 0;
                for ($i = 0; $i < 5; $i++) {
                    foreach ($result as $row => $cols) {
                        foreach ($cols as $col => $value) {
                            if (is_numeric($value)) {
                                if (array_key_exists($row, $u)) {
                                    $v[$col] = $in[$row][$col] - $u[$row];
                                }
                                if (array_key_exists($col, $v)) {
                                    $u[$row] = $in[$row][$col] - $v[$col];
                                }
                            }
                        }
                    }
                }

                return [$u, $v];
            };

            [$u, $v] = $uv();
            ksort($u);
            ksort($v);

            $this->template->result2[] = [$v, $u, $result];

            $max = 1;
            $delta = 0;

            foreach ($in as $row => $cols) {
                foreach ($cols as $col => $value) {
                    if ($result[$row][$col] == 'x') {
                        $delta = $u[$row] + $v[$col] - $value;
                        if ($delta >= $max) {
                            $max = $delta;
                            $findLoop($row, $col);
                        }
                    }
                }
            }

            if (count($loop) > 0) {
                /*
                $show = [];
                foreach ($loop as $e) {
                    $show[] = sprintf('(%s)', implode(', ', $e));
                }
                dump(implode(', ', $show));
                */
                [$xMin, $yMin] = $loop[1];
                $min = $result[$xMin][$yMin];
                foreach ($loop as $i => $e) {
                    if ($i%2 != 0) {
                        if ($result[$e[0]][$e[1]] < $min) {
                            $min = $result[$e[0]][$e[1]];
                            $xMin = $e[0];
                            $yMin = $e[1];
                        } else if ($result[$e[0]][$e[1]] == $min) {
                            if ($in[$e[0]][$e[1]] > $in[$xMin][$yMin]) {
                                $min = $result[$e[0]][$e[1]];
                                $xMin = $e[0];
                                $yMin = $e[1];
                            }
                        }
                    }
                }

                $result[$loop[0][0]][$loop[0][1]] = 'T';
                $this->template->result2[] = [$v, $u, $result];
                $result[$loop[0][0]][$loop[0][1]] = 0;

                foreach ($loop as $i => $e) {
                    if ($i%2 == 0) {
                        $result[$e[0]][$e[1]] += $min;
                    } else {
                        $result[$e[0]][$e[1]] -= $min;
                    }
                }

                $result[$xMin][$yMin] = 'L';
                $this->template->result2[] = [$v, $u, $result];
                $result[$xMin][$yMin] = 'x';
            }
    }

    public function renderDefault()
    {
    }
}
