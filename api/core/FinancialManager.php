<?php

namespace LogicLeap\PhpServerCore;

use LogicLeap\PhpServerCore\data_types\Decimal;

class FinancialManager
{
    // Liquidity Ratios

    // Liquidity ratios are financial ratios that measure a company’s ability to repay both short- and long-term obligations.


    /**
     * The current ratio measures a company’s ability to pay off short-term liabilities with current assets
     * @param Decimal $currentAssets
     * @param Decimal $currentLiabilities
     * @return Decimal
     */
    public static function currentRatio(Decimal $currentAssets, Decimal $currentLiabilities): Decimal
    {
        return $currentAssets->div($currentLiabilities);
    }

    /**
     * The acid-test ratio measures a company’s ability to pay off short-term liabilities with quick assets
     * @param Decimal $currentAssets
     * @param Decimal $inventories
     * @param Decimal $currentLiabilities
     * @return Decimal
     */
    public static function acidTestRatio(Decimal $currentAssets, Decimal $inventories, Decimal $currentLiabilities): Decimal
    {
        return $currentAssets->sub($inventories)->div($currentLiabilities);
    }

    /**
     * The cash ratio measures a company’s ability to pay off short-term liabilities with cash and cash equivalents
     * @param Decimal $cash
     * @param Decimal $currentLiabilities
     * @return Decimal
     */
    public static function cashRatio(Decimal $cash, Decimal $currentLiabilities): Decimal
    {
        return $cash->div($currentLiabilities);
    }

    /**
     * The operating cash flow ratio is a measure of the number of times a company can pay off current liabilities
     * with the cash generated in a given period
     * @param Decimal $operatingCashFlow
     * @param Decimal $currentLiabilities
     * @return Decimal
     */
    public static function operatingCashFlowRatio(Decimal $operatingCashFlow, Decimal $currentLiabilities): Decimal
    {
        return $operatingCashFlow->div($currentLiabilities);
    }



    // Leverage Financial Ratios

    // Leverage ratios measure the amount of capital that comes from debt. In other words, leverage financial ratios
    // are used to evaluate a company’s debt levels.


    /**
     * The debt ratio measures the relative amount of a company’s assets that are provided from debt
     * @param Decimal $totalLiabilities
     * @param Decimal $totalAssets
     * @return Decimal
     */
    public static function debtRatio(Decimal $totalLiabilities, Decimal $totalAssets): Decimal
    {
        return $totalLiabilities->div($totalAssets);
    }

    /**
     * The debt to equity ratio calculates the weight of total debt and financial liabilities against shareholders’ equity
     * @param Decimal $totalLiabilities
     * @param Decimal $shareholdersEquity
     * @return Decimal
     */
    public static function debtToEquityRatio(Decimal $totalLiabilities, Decimal $shareholdersEquity): Decimal
    {
        return $totalLiabilities->div($shareholdersEquity);
    }

    /**
     * The interest coverage ratio shows how easily a company can pay its interest expenses
     * @param Decimal $operatingIncome
     * @param Decimal $interestExpenses
     * @return Decimal
     */
    public static function interestCoverageRatio(Decimal $operatingIncome, Decimal $interestExpenses): Decimal
    {
        return $operatingIncome->div($interestExpenses);
    }

    /**
     * The debt service coverage ratio reveals how easily a company can pay its debt obligations
     * @param Decimal $operatingIncome
     * @param Decimal $totalDebtService
     * @return Decimal
     */
    public static function debtServiceCoverageRatio(Decimal $operatingIncome, Decimal $totalDebtService): Decimal
    {
        return $operatingIncome->div($totalDebtService);
    }



    // Efficiency Ratios

    // Efficiency ratios, also known as activity financial ratios, are used to measure how well a company is utilizing
    // its assets and resources.


    /**
     * The asset turnover ratio measures a company’s ability to generate sales from assets
     * @param Decimal $netSales
     * @param Decimal $averageTotalAssets
     * @return Decimal
     */
    public static function assetTurnOverRatio(Decimal $netSales, Decimal $averageTotalAssets): Decimal
    {
        return $netSales->div($averageTotalAssets);
    }

    /**
     * The inventory turnover ratio measures how many times a company’s inventory is sold and replaced over a given period
     * @param Decimal $costOfGoodsSold
     * @param Decimal $averageInventory
     * @return Decimal
     */
    public static function inventoryTurnOverRatio(Decimal $costOfGoodsSold, Decimal $averageInventory): Decimal
    {
        return $costOfGoodsSold->div($averageInventory);
    }

    /**
     * The accounts receivable turnover ratio measures how many times a company can turn receivables into cash over a given period
     * @param Decimal $netCreditSales
     * @param Decimal $averageAccountsReceivable
     * @return Decimal
     */
    public static function receivablesTurnOverRatio(Decimal $netCreditSales, Decimal $averageAccountsReceivable): Decimal
    {
        return $netCreditSales->div($averageAccountsReceivable);
    }

    /**
     * The days sales in inventory ratio measures the average number of days that a company holds on to inventory
     * before selling it to customers
     * @param Decimal $inventoryTurnOverRatio
     * @return Decimal
     */
    public static function daysSalesInInventoryRatio(Decimal $inventoryTurnOverRatio): Decimal
    {
        return (new Decimal('365'))->div($inventoryTurnOverRatio);
    }



    // Profitability Ratios

    // Profitability ratios measure a company’s ability to generate income relative to revenue, balance sheet assets,
    // operating costs, and equity


    /**
     * The gross margin ratio compares the gross profit of a company to its net sales to show how much profit a company
     * makes after paying its cost of goods sold
     * @param Decimal $grossProfit
     * @param Decimal $netSales
     * @return Decimal
     */
    public static function grossMarginRatio(Decimal $grossProfit, Decimal $netSales): Decimal
    {
        return $grossProfit->div($netSales);
    }

    /**
     * The operating margin ratio, sometimes known as the return on sales ratio, compares the operating income of a
     * company to its net sales to determine operating efficiency
     * @param Decimal $operatingIncome
     * @param Decimal $netSales
     * @return Decimal
     */
    public static function operatingMarginRatio(Decimal $operatingIncome, Decimal $netSales): Decimal
    {
        return $operatingIncome->div($netSales);
    }

    /**
     * The return on assets ratio measures how efficiently a company is using its assets to generate profit
     * @param Decimal $netIncome
     * @param Decimal $totalAssets
     * @return Decimal
     */
    public static function returnOnAssetsRatio(Decimal $netIncome, Decimal $totalAssets): Decimal
    {
        return $netIncome->div($totalAssets);
    }

    /**
     * The return on equity ratio measures how efficiently a company is using its equity to generate profit
     * @param Decimal $netIncome
     * @param Decimal $shareholdersEquity
     * @return Decimal
     */
    public static function returnOnEquityRatio(Decimal $netIncome, Decimal $shareholdersEquity): Decimal
    {
        return $netIncome->div($shareholdersEquity);
    }



    // Market Value Ratios

    // Market value ratios are used to evaluate the share price of a company’s stock.


    /**
     * The book value per share ratio calculates the per-share value of a company based on the equity available to shareholders
     * @param Decimal $shareholdersEquity
     * @param Decimal $preferredEquity
     * @param Decimal $totalCommonSharesOutStanding
     * @return Decimal
     */
    public static function bookValuePerShareRatio(Decimal $shareholdersEquity, Decimal $preferredEquity,
                                                  Decimal $totalCommonSharesOutStanding): Decimal
    {
        return $shareholdersEquity->sub($preferredEquity)->div($totalCommonSharesOutStanding);
    }

    /**
     * The dividend yield ratio measures the amount of dividends attributed to shareholders relative to the market value per share
     * @param Decimal $dividendPerShare
     * @param Decimal $sharePrice
     * @return Decimal
     */
    public static function DividedYieldRatio(Decimal $dividendPerShare, Decimal $sharePrice): Decimal
    {
       return  $dividendPerShare->div($sharePrice);
    }

    /**
     * The earnings per share ratio measures the amount of net income earned for each share outstanding
     * @param Decimal $netEarnings
     * @param Decimal $totalSharesOutstanding
     * @return Decimal
     */
    public static function earningsPerShareRatio(Decimal $netEarnings, Decimal $totalSharesOutstanding): Decimal
    {
        return $netEarnings->div($totalSharesOutstanding);
    }

    /**
     * The price-earnings ratio compares a company’s share price to its earnings per share
     * @param Decimal $sharePrice
     * @param Decimal $earningsPerShare
     * @return Decimal
     */
    public static function priceEarningsRatio(Decimal $sharePrice, Decimal $earningsPerShare): Decimal
    {
        return $sharePrice->div($earningsPerShare);
    }
}