<?php

namespace Encompass\Fields;

class LoanFields
{
    const DEFAULT_ENTITIES = [
        'Application',
        'Borrower',
        'CoBorrower',
        'BuyDown',
        'Income',
        'SelfEmployedIncome',
        'Contact', 'Funding',
        'ReoProperty', 'ATRQMCommon',
        'Loan',
        'HudLoanData',
        'PrequalificationScenario',
        'ClosingCost',
        'ClosingDocument',
        'Hmda',
        'Employment',
        'Residence',
        'Miscellaneous',
        'StatementCreditDenial',
        'MilitaryService',
        'TQLFraudAlert',
        'LoanProgram',
        'Prequalification',
        'UnderwriterSummary',
        'AUSTrackingLog',
        'ATRQMBorrower',
        'GfePayment',
        'ExtraPayment',
        'PaymentTransaction',
        'FhaVaLoan',
        'PreviousVaLoan',
        'ExtraPayment',
        'Form',
        'Property',
        'Asset',
        'Liability',
        'ClosingCost',
        'ClosingDocument',
        'Contact',
        'Tsum',
        'FreddieMac',
        'CustomField',
        'FundingFee',
        'LoanProductData',
        'CommitmentTerms',
        'EmDocument',
        'Hud1Es',
        'LoanProductData',
        'Mcaw',
        'PrivacyPolicy',
        'Property',
        'PurchaseCredit',
        'RegulationZ',
        'RateLock',
        'TPO',
        'TQLReportInformation',
        'TQLFraudAlert',
        'TQL',
        'Section32',
        'Uldd',
        'UsdaHouseholdIncome',
        'StateDisclosure',
        'Usda', 'OtherTransaction',
        'VaLoanData',
        'VirtualFields',
        'EdmDocument'
    ];

    public static function getFields()
    {
        return array_values(self::DEFAULT_ENTITIES);
    }

}