<?php

namespace LogicLeap\PhpServerCore\data_types;

use ValueError;

class Decimal
{
    private int $decimalPlaces = 4;
    private string $number;

    public function __construct(string $number, int $decimalPlaces = 4)
    {
        $this->decimalPlaces = $decimalPlaces;

        if (!preg_match('/^-?([0-9]+([.][0-9]*)?|[.][0-9]+)$/', $number))
            throw new ValueError('Invalid decimal number.');

        $this->number = $number;
    }

    /**
     * @return string Returns decimal number as a string.
     */
    public function getDecimal():string
    {
        return $this->number;
    }

    public function setPrecision(int $decimalPlaces): void
    {
        $this->decimalPlaces = $decimalPlaces;
    }

    public function add(Decimal $num): Decimal
    {
        return new Decimal(bcadd($this->number, $num->number, $this->decimalPlaces), $this->decimalPlaces);
    }

    public function sub(Decimal $subtrahend): Decimal
    {
        return new Decimal(bcsub($this->number, $subtrahend->number, $this->decimalPlaces), $this->decimalPlaces);
    }
    
    public function mul(Decimal $multiplier): Decimal
    {
        return new Decimal(bcmul($this->number, $multiplier->number, $this->decimalPlaces), $this->decimalPlaces);
    }

    public function div(Decimal $divisor): Decimal
    {
        return new Decimal(bcdiv($this->number, $divisor->number, $this->decimalPlaces), $this->decimalPlaces);
    }

    public function pow(Decimal $exponent): Decimal
    {
        return new Decimal(bcpow($this->number, $exponent->number, $this->decimalPlaces), $this->decimalPlaces);
    }


    public function mod(Decimal $divisor): Decimal
    {
        return new Decimal(bcmod($this->number, $divisor->number, $this->decimalPlaces), $this->decimalPlaces);
    }

    public function sqrt(Decimal $root): Decimal
    {
        return new Decimal(bcsqrt($this->number, $root->number, $this->decimalPlaces), $this->decimalPlaces);
    }

    public function __toString(): string
    {
        return $this->number;
    }
}