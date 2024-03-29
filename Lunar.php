<?php
/**
 * User: mayi
 * Date: 2019/10/11
 * Time: 14:26
 */

/**
 * 阳历转换为阴历
 * Class Lunar
 */
class Lunar
{
/**
        $lunar = new Lunar(strtotime("2019-03-02"));
        echo $lunar->getAnimalYear()."年\r\n";     猪年
        echo $lunar->getLunarYear()."年\r\n";      己亥年
        echo $lunar->getLunarMonth()."月\r\n";     丙寅月
        echo $lunar->getLunarDay()."日\r\n";       戊戌日
        echo "阴历：".$lunar->getLunarMonthDay()."\r\n";   阴历：一月廿六

**/

    private $lunarInfo = array(
        0x04bd8,0x04ae0,0x0a570,0x054d5,0x0d260,0x0d950,0x16554,0x056a0,0x09ad0,0x055d2,
        0x04ae0,0x0a5b6,0x0a4d0,0x0d250,0x1d255,0x0b540,0x0d6a0,0x0ada2,0x095b0,0x14977,
        0x04970,0x0a4b0,0x0b4b5,0x06a50,0x06d40,0x1ab54,0x02b60,0x09570,0x052f2,0x04970,
        0x06566,0x0d4a0,0x0ea50,0x06e95,0x05ad0,0x02b60,0x186e3,0x092e0,0x1c8d7,0x0c950,
        0x0d4a0,0x1d8a6,0x0b550,0x056a0,0x1a5b4,0x025d0,0x092d0,0x0d2b2,0x0a950,0x0b557,
        0x06ca0,0x0b550,0x15355,0x04da0,0x0a5d0,0x14573,0x052d0,0x0a9a8,0x0e950,0x06aa0,
        0x0aea6,0x0ab50,0x04b60,0x0aae4,0x0a570,0x05260,0x0f263,0x0d950,0x05b57,0x056a0,
        0x096d0,0x04dd5,0x04ad0,0x0a4d0,0x0d4d4,0x0d250,0x0d558,0x0b540,0x0b5a0,0x195a6,
        0x095b0,0x049b0,0x0a974,0x0a4b0,0x0b27a,0x06a50,0x06d40,0x0af46,0x0ab60,0x09570,
        0x04af5,0x04970,0x064b0,0x074a3,0x0ea50,0x06b58,0x055c0,0x0ab60,0x096d5,0x092e0,
        0x0c960,0x0d954,0x0d4a0,0x0da50,0x07552,0x056a0,0x0abb7,0x025d0,0x092d0,0x0cab5,
        0x0a950,0x0b4a0,0x0baa4,0x0ad50,0x055d9,0x04ba0,0x0a5b0,0x15176,0x052b0,0x0a930,
        0x07954,0x06aa0,0x0ad50,0x05b52,0x04b60,0x0a6e6,0x0a4e0,0x0d260,0x0ea65,0x0d530,
        0x05aa0,0x076a3,0x096d0,0x04bd7,0x04ad0,0x0a4d0,0x1d0b6,0x0d250,0x0d520,0x0dd45,
        0x0b5a0,0x056d0,0x055b2,0x049b0,0x0a577,0x0a4b0,0x0aa50,0x1b255,0x06d20,0x0ada0
    );
    private $animals = array("鼠","牛","虎","兔","龙","蛇","马","羊","猴","鸡","狗","猪");
    private $gan = array("甲","乙","丙","丁","戊","己","庚","辛","壬","癸");
    private $zhi = array("子","丑","寅","卯","辰","巳","午","未","申","酉","戌","亥");


    public $year = 0;
    public $month = 0;
    public $day = 0;

    public $dayCyl = 0;
    public $monCyl = 0;
    public $yearCyl = 0;

    /**
     * 获取地支年（鼠牛..)
     * @return mixed
     */
    public function getAnimalYear(){
        return $this->animals[($this->year-4)%12];
    }

    /**
     * 获取干支年
     * @return mixed
     */
    public function getLunarYear(){
        return $this->cyclical($this->year-1900+36);
    }

    /**
     * 获取干支月
     * @return string
     */
    public function getLunarMonth(){
        return $this->cyclical($this->monCyl);
    }

    /**
     * 获取干支日
     * @return string
     */
    public function getLunarDay(){
        return $this->cyclical($this->dayCyl);
    }

    /**
     * 获取农历月日
     * @return string
     */
    public function getLunarMonthDay(){
        return $this->cDay($this->month,$this->day);
    }

    public function __construct($date = null)
    {

        //==== 算出农历, 传入日期物件, 传回农历日期物件
        //     该物件属性有 .year .month .day .isLeap .yearCyl .dayCyl .monCyl
        $objDate = strtotime(date("Y-m-d", $date)) * 1000;
        if(!$objDate || $objDate<=0){
            $objDate = floor(microtime(true)*1000);
        }
        $offset = ($objDate + 2206425943000) / 86400000 ;

        $this->dayCyl = $offset + 40;
        $this->monCyl = 14;

        $temp = 0;
        for($i=1900; $i<2050 && $offset>0; $i++) {
            $temp = $this->lYearDays($i);
            $offset -= $temp;
            $this->monCyl += 12;
        }

        if($offset<0) {
            $offset += $temp;
            $i--;
            $this->monCyl -= 12;
        }

        $this->year = $i;
        $this->yearCyl = $i-1864;

        $leap = $this->leapMonth($i); //闰哪个月
        $isLeap = false;

        for($i=1; $i<13 && $offset>0; $i++) {
            //闰月
            if($leap>0 && $i==($leap+1) && $isLeap==false)
            {
                --$i;
                $isLeap = true;
                $temp = $this->leapDays($this->year); }
            else
            {
                $temp = $this->monthDays($this->year, $i);
            }

            //解除闰月
            if($isLeap==true && $i==($leap+1)) {
                $isLeap = false;
            }

            $offset -= $temp;
            if($isLeap == false) {
                $this->monCyl++;
            }
        }

        if($offset==0 && $leap>0 && $i==$leap+1) {
            if ($isLeap) {
                $isLeap = false;
            } else {
                $isLeap = true;
                --$i;
                --$this->monCyl;
            }
        }

        if($offset<0){
            $offset += $temp;
            --$i;
            --$this->monCyl;
        }

        $this->month = $i;
        $this->day = $offset + 1;
    }

    //==== 传入 offset 传回干支, 0=甲子
    private function cyclical($num) {
        return $this->gan[$num%10] . $this->zhi[$num%12];
    }

    //==== 传回农历 y年的总天数
    private function lYearDays($year) {
        $sum = 348;
       for($i=0x8000; $i>0x8; $i>>=1) {
           $sum += ($this->lunarInfo[$year-1900] & $i)? 1: 0;
        }
       return $sum+$this->leapDays($year);
    }

    //==== 传回农历 y年闰月的天数
    private function leapDays($year) {
        if($this->leapMonth($year)){
            return ($this->lunarInfo[$year-1900] & 0x10000)? 30: 29;
        }
       else {
           return 0;
       }
    }

    //==== 传回农历 y年闰哪个月 1-12 , 没闰传回 0
    private function leapMonth($year) {
        return $this->lunarInfo[$year-1900] & 0xf;
    }

    //====================================== 传回农历 y年m月的总天数
    private function monthDays($year,$month) {
        return ($this->lunarInfo[$year-1900] & (0x10000>>$month))? 30: 29;
    }

    //==== 中文日期
    private function cDay ($month,$day){
        $nStr1 = array('日','一','二','三','四','五','六','七','八','九','十');
        $nStr2 = array('初','十','廿','卅','　');
        $s =  "";
        if ($month>10){
            $s = '十'.$nStr1[$month-10];
        } else {
            $s = $nStr1[$month];
        }
        $s .= '月';
        switch ($day) {
            case 10:$s .= '初十'; break;
            case 20:$s .= '二十'; break;
            case 30:$s .= '三十'; break;
            default:$s .= $nStr2[floor($day/10)];
            $s .= $nStr1[$day%10];
        }
        return $s;
    }

    //中文日期
    private function chineseDay($day){
        $nStr1 = array('日','一','二','三','四','五','六','七','八','九','十');
        $nStr2 = array('初','十','廿','卅','　');
        $s = "";
        switch ($day) {
            case 10:$s += '初十'; break;
            case 20:$s += '二十'; break;
            case 30:$s += '三十'; break;
            default:
                $s += $nStr2[floor($day/10)];
                $s += $nStr1[$day%10];
        }
        return $s;
    }

}
