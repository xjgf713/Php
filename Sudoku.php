<?php
/**
 * Created by PhpStorm.
 * User: jingaofeng
 * Date: 2018/10/30
 * Time: 下午6:15
 */

namespace sudoku;

/**
 * 数独
 * Class Sudoku
 * @package console\controllers\test
 */
class Sudoku
{
    private static $dimension = 0;
    private static $sudoku = [];    //结果集
    private static $dimensionArray = [];    //可填充数字
    private static $location = [];  //坐标位置

    private static $last = [];  //上一次的记录,错误回退

    private static $count = 0;

    private static $xTag = true;  //水平排列 标志位
    private static $formatTag = false;

    private static $level = 0;

    /**
     */
    /**
     * 创建数独
     * @command php yii test/mysql/make-sudoku
     * @param int $dimension 维度
     * @param int $version 版本
     */
    public function actionMakeSudoku($dimension, $version = 1)
    {
        $result = [];
        //数独维度
        self::$dimension = $dimension;

        for ($i = 0; $i < $dimension; $i++) {
            self::$dimensionArray[$i + 1] = $dimension;
            for ($k = 0; $k < $dimension; $k++) {
                $tl = $i . '.' . $k;
                self::$location[] = $tl;
            }
        }

        switch ($version) {
            case 1:
                while (self::$location) {
                    $this->startMake();

                    $tag = $this->validateSudoku();
                    if ($tag) {
                        self::$location = [];
                    }
                    if (count(self::$location) >= self::$dimension * self::$dimension) {
                        dd(self::$location, 55555, self::$dimensionArray, 11);
                    }
                    self::$count++;
                }
                break;
            case 2:
                /**
                 * 从第一排开始排，不随机位置排，
                 * 如果遇到错误位，用回形针算法 优化错误位置
                 */
                $tempD = 0;
                while ($tempD < self::$dimension) {
                    $this->appendLineV2($tempD);
                    $tempD++;
                }
                break;
        }


        $this->printSudoku();
        $validate = $this->validateSudoku();
        dd('success--' . self::$count . '----' . $validate);
    }

    /**
     * 打印 数独
     * @return bool
     */
    private function printSudoku()
    {
        for ($i = 0; $i < self::$dimension; $i++) {
            for ($k = 0; $k < self::$dimension; $k++) {
                if (isset(self::$sudoku[$i][$k])) {
                    echo self::$sudoku[$i][$k], '-';
                } else {
                    echo '0-';
                }
            }
            echo "\r\n";
        }
        return true;
    }

    private function startMake()
    {
        list($selectLocation, $num) = $this->newSelect();

        //如果陷入死循环，回退一个，扣掉一个位置 重新填
        if (!empty(self::$last) && $num == self::$last[0] && $selectLocation == self::$last[1]) {
            $x = array_rand(self::$sudoku);
            $y = array_rand(self::$sudoku[$x]);
            $this->fallback(self::$sudoku[$x][$y], $x . '.' . $y);
        }

//        echo 'location--' . $selectLocation . "\r\n";
//        echo 'num--' . $num . "\r\n";


//        //验证 位置和数字合法性
        $validate = $this->validateAppend($num, $selectLocation);
        if (!$validate) {
            $this->fallback($num, $selectLocation);
            //回退 结果集
            $explode = explode('.', $selectLocation);
            $x = $explode[0];
            $y = $explode[1];
            unset(self::$sudoku[$x][$y]);
        }
        self::$last = [
            $num,
            $selectLocation
        ];
    }

    /**
     * 筛选 新位置，新填充数字
     * @return array
     */
    private function newSelect()
    {
        $selectLocation = null;
        $num = null;

        //挑选一个位置，
        $selectLocation = $this->selectLocation();

        //挑选一个数字
        $num = $this->selectNum();

        return [
            $selectLocation,
            $num,
        ];
    }

    /**
     * 挑选一个位置
     * @return mixed
     */
    private function selectLocation()
    {
        $rand = array_rand(self::$location);
        $selectLocation = self::$location[$rand];

        unset(self::$location[$rand]);
        return $selectLocation;
    }

    /**
     * 挑选一个数字
     * @return mixed
     */
    private function selectNum()
    {
        $keys = array_keys(self::$dimensionArray);
        if (empty($keys)) {
            self::$location = [];
        }

        $selectKey = $keys[array_rand($keys)];

        self::$dimensionArray[$selectKey] -= 1;
        if (self::$dimensionArray[$selectKey] == 0) {
            unset(self::$dimensionArray[$selectKey]);
        }
        return $selectKey;
    }

    /**
     * 筛选退回
     * @param $num
     * @param $selectLocation
     */
    private function fallback($num, $selectLocation)
    {
        self::$location[] = $selectLocation;
        self::$location = array_unique(self::$location);

        if (isset(self::$dimensionArray[$num])) {
            self::$dimensionArray[$num]++;
        } else {
            self::$dimensionArray[$num] = 1;
        }
    }

    /**
     * 验证 填充合法性
     * @param $newNum
     * @param $location
     * @return bool
     */
    private function validateAppend($newNum, $location)
    {
        $explode = explode('.', $location);
        $x = $explode[0];
        $y = $explode[1];

        //验证 横纵坐标 是否有重复数字
        for ($i = 0; $i < self::$dimension; $i++) {
            if (isset(self::$sudoku[$x][$i]) && self::$sudoku[$x][$i] == $newNum) {
                return false;
            }

            if (isset(self::$sudoku[$i][$y]) && self::$sudoku[$i][$y] == $newNum) {
                return false;
            }
        }
        self::$sudoku[$x][$y] = $newNum;
        return true;
    }

    /**
     * 检查是否 完善
     * @return bool
     */
    private function validateSudoku()
    {
        $tag = 11;
        for ($i = 0; $i < self::$dimension; $i++) {
            for ($k = 0; $k < self::$dimension; $k++) {
                if (!isset(self::$sudoku[$i][$k])) {
                    $tag = 00;
                }

                $numWidth = $this->getWidthNumsV2($i, $k);
                if (count(array_unique($numWidth)) != count($numWidth)) {
                    $tag = 00;
                }

                $numLength = $this->getLengthNumsV2($i, $k);
                if (count(array_unique($numLength)) != count($numLength)) {
                    $tag = 00;
                }
            }
        }
        return $tag;
    }

    private function checkWlV2()
    {
        $tag = true;
        for ($i = 0; $i < self::$dimension; $i++) {
            for ($k = 0; $k < self::$dimension; $k++) {
                $numWidth = $this->getWidthNumsV2($i, $k);
                if (count(array_unique($numWidth)) != count($numWidth)) {
                    $tag = false;
                }

                $numLength = $this->getLengthNumsV2($i, $k);
                if (count(array_unique($numLength)) != count($numLength)) {
                    $tag = false;
                }
            }
        }
        return $tag;
    }


    private function appendLineV2($line)
    {
        for ($i = 0; $i < self::$dimension; $i++) {
            self::$xTag = true;
            $this->makeLocationV2($line, $i);

            $vv = $this->checkWlV2();
            if (!$vv) {
                //中间填充错误 debug
                dd(4444445555, $this->printSudoku());
            }
        }
    }


    private function makeLocationV2($x, $y)
    {
        list($tag, $selectNum) = $this->selectNumV2($x, $y);

        if ($selectNum == -1) {
            //回退上一个位置,回形针 设置当前位置，
            $this->cicleBackV2($x, $y);
        } else {
            $this->setLocationV2($x, $y, $selectNum);
        }
    }

    /**
     * 给当前位置 筛选一个不重复的数字
     * @param $x
     * @param $y
     * @return mixed
     */
    private function selectNumV2($x, $y)
    {
        $numArray = range(1, self::$dimension, 1); //填充范围值

        //查找横排 已有的填充数字
        $numsWidth = [];
        if (isset(self::$sudoku[$x])) {
            $numsWidth = array_values(self::$sudoku[$x]);
        }

        //竖排已填充数字
        $numsLegth = [];
        for ($i = 0; $i < self::$dimension; $i++) {
            if (isset(self::$sudoku[$i][$y])) {
                $numsLegth[] = self::$sudoku[$i][$y];
            }
        }

        //当前位置数字
        $current = [];
        $fallTag = false;
        if (isset(self::$sudoku[$x][$y])) {
            $current = [
                self::$sudoku[$x][$y]
            ];
            $numsWidth = array_diff($numsWidth, $current);
            $numsLegth = array_diff($numsLegth, $current);
            $fallTag = true;    //回退标志
        }


        //横纵 去重
        $diffArray = array_diff($numArray, $numsWidth, $numsLegth);
        if ($fallTag) {
            //如果是回退， 不重复筛选 已出现过的数字
            $diffArray = array_diff($diffArray, $current);
        }

        if (empty($diffArray)) {
            $selectNum = -1;    //当前位置无 可填充数字
        } else {
            $selectNum = $diffArray[array_rand($diffArray)];
        }

        return [
            $fallTag,
            $selectNum
        ];
    }

    private function setLocationV2($x, $y, $num)
    {
        self::$count++;
        self::$sudoku[$x][$y] = $num;
    }

    /**
     * 回退到上一个位置
     */
    private function fallbackV2($x, $y)
    {
        $numWidth = [];
        if (isset(self::$sudoku[$x])) {
            //横排 已填充数字
            $numWidth = array_values(self::$sudoku[$x]);
        }

        $numArray = range(1, self::$dimension, 1);
        //重新设置 上一个位置
        $last = self::$sudoku[$x][$y - 1];
        $newWidth = array_diff($numArray, array_diff($numWidth, [$last]));

        $newNum = $newWidth[array_rand($newWidth)];

        self::$sudoku[$x][$y - 1] = $newNum;
        self::$count++;

        return $newNum;
    }

    private function formatNewLineV2($x)
    {
        $numArray = range(1, self::$dimension, 1);
        for ($i = 0; $i < self::$dimension; $i++) {

        }
    }

    private function cicleBackV2($x, $y)
    {

        $numArray = range(1, self::$dimension, 1);

        $numsWidth = [];
        if (isset(self::$sudoku[$x])) {
            $numsWidth = array_values(self::$sudoku[$x]);
        }

        $diff = array_diff($numArray, $numsWidth);

        $selectOne = $diff[array_rand($diff)];

        //该位置 确定一个
        $this->setLocationV2($x, $y, $selectOne);


        //判断该 排列方向的 垂直位，检测
        self::$formatTag = true;

        self::$level = 0;
        $this->diguiV2($x, $y);
    }


    private function checkFormatV2($x, $y)
    {
        //判断是否 需要修正
        $formatTag = false;
        if (self::$xTag) {
            $numLenth = $this->getLengthNumsV2($x, $y);
            if (count(array_unique($numLenth)) != count($numLenth)) {
                $formatTag = true;
            }
        } else {
            $numWidth = $this->getWidthNumsV2($x, $y);
            if (count(array_unique($numWidth)) != count($numWidth)) {
                $formatTag = true;
            }
        }
        return $formatTag;
    }

    private function diguiV2($x, $y)
    {
        self::$level++;
        if (self::$level >= 220) {
            //死循环 debug
            dd($this->printSudoku(), 111111, $x, $y, self::$formatTag, self::$xTag);
        }

        while (self::$formatTag) {
            list($newX, $newY) = $this->verticalSetV2($x, $y);
            if (self::$formatTag) {
                $this->diguiV2($newX, $newY);
            }
        }

        return 1;
    }


    private function verticalSetV2($x, $y)
    {
        $tag = $this->checkFormatV2($x, $y);
        $newX = -1;
        $newY = -1;

        if ($tag) {
            $numArray = range(1, self::$dimension, 1);
            if (self::$xTag) {
                //如果是水平标志，查找 垂直位，是否有不合法 数字

                //不能回形针检测， 必须除本身位，都检测
                for ($i = 0; $i < self::$dimension; $i++) {
                    if (isset(self::$sudoku[$i][$y]) && self::$sudoku[$i][$y] == self::$sudoku[$x][$y] && $i != $x) {
                        $numLenth = $this->getLengthNumsV2($x, $y);

                        $diffLength = array_diff($numArray, $numLenth);

                        $verticalOne = $diffLength[array_rand($diffLength)];

                        $this->setLocationV2($i, $y, $verticalOne);

                        //标志位 置反
                        self::$xTag = !self::$xTag;

                        $newX = $i;
                        $newY = $y;
                        break;
                    }
                }
            } else {
                //如果是垂直标志，查找 水平位， 是否有不合法数字
                for ($k = 0; $k < self::$dimension; $k++) {
                    if (isset(self::$sudoku[$x][$k]) && self::$sudoku[$x][$k] == self::$sudoku[$x][$y] && $k != $y) {
                        $numWidth = $this->getWidthNumsV2($x, $y);

                        $diffWidth = array_diff($numArray, $numWidth);

                        $widthOne = $diffWidth[array_rand($diffWidth)];

                        $this->setLocationV2($x, $k, $widthOne);

                        self::$xTag = !self::$xTag;
                        $newX = $x;
                        $newY = $k;
                        break;
                    }
                }
            }
        } else {
            self::$formatTag = false;
        }

        return [
            $newX,
            $newY
        ];
    }

    /**
     * 获取 垂直位置的 已填充数字
     * @param $x
     * @param $y
     */
    private function getLengthNumsV2($x, $y)
    {
        $numsLegth = [];
        for ($i = 0; $i < self::$dimension; $i++) {
            if (isset(self::$sudoku[$i][$y])) {
                $numsLegth[] = self::$sudoku[$i][$y];
            }
        }
        return $numsLegth;
    }

    /**
     * 获取 水平位置的 已填充数字
     * @param $x
     * @param $y
     */
    private function getWidthNumsV2($x, $y)
    {
        $numsWidth = [];
        if (isset(self::$sudoku[$x])) {
            $numsWidth = array_values(self::$sudoku[$x]);
        }
        return $numsWidth;
    }
}


