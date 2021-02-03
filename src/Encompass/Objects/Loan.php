<?php

namespace Encompass\Objects;

use Encompass\ApiRequest;
use Encompass\Fields\LoanFields;
use Encompass\Fields\URI;
use Illuminate\Support\Facades\Storage;

class Loan
{
    protected $api;
    protected $responseXml;

    /**
     * Loan constructor.
     * @param $api
     */
    public function __construct($user)
    {
        $this->api = new ApiRequest($user);
    }

    private function getEndpoint()
    {
        // return '/encompass/v1/loans/';
        return '/encompass/v3/loans/';
    }

    public function getSelf($id)
    {
        return $this->getLoanById($id);
    }

    protected function defaultParameters()
    {
        return implode(',',  LoanFields::getFields());
    }

    /**
     * @param $id
     * @return \Encompass\EncompassResponse
     * @throws \Encompass\Exceptions\EncompassResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getLoanById($id)
    {
        return $this->api->get(
            URI::uri('loans') . $id,
            [
                'query' => [
                    'entities' => $this->defaultParameters(),
                    'metadata' => false
                ]
            ]
        );
    }

    /**
     * @param $applications
     * @param $folder
     * @return \Encompass\EncompassResponse
     */
    public function createLoan($applications, $folder)
    {
        return $this->newImport($applications, $folder);
    }



    protected function newImport($applications, string $folder)
    {
            // dd(json_decode($applications));
        return $this->api->post(
            URI::uri('import', $folder), [
                'json' => $applications
            ],
            ['Content-Type' => 'application/json']
        );
    }   

    public function createLoanFolder($id, ...$params)
    {
        return $this->api->post(
            URI::uri('create-folder', $id), [
                'json' => [
                    'title' => $params[0],
                    'fileWithExtension' => $id . '.' . $params[0] . '.pdf',
                    'createReason' => 1
                ]
            ],
            ['Content-Type' => 'application/json']
        );
    }

    public function attachmentLoanRequest(string $allowedUrl, $kycId, $content)
    {
        return $this->api->put($allowedUrl, [
            'multipart' => array(
                [
                    'name' => $kycId . '.pdf',
                    'contents' => $content
                ]
            ),
        ], ['Content-Type' => 'multipart/form-data']);
    }

    public function converXmltoJson($xml)
    {
        $myArray = $this->api->post(
            URI::uri('xmlJson'),
            [
                'body' => $xml
            ],
            ['Content-Type' => 'application/vnd.elliemae.mismo34+xml']
        )->toCollection();

        $myArray = $this->array_filter_recursive($myArray->toArray());
        //$json = '{"applications":[{"id":"2b716950-f08f-4d6b-a0c3-d369eeb7cf03","borrower":{"applicantType":"Borrower","applicationTakenMethodType":"Internet","bankruptcyIndicator":false,"birthDate":"2000-01-01","employment":[{"id":"loan:application:0:employment:1","currentEmploymentIndicator":true,"employerName":"web","employmentStartDate":"2016-02-01","specialEmployerRelationshipIndicator":false,"selfEmployedIndicator":false},{"id":"loan:application:0:employment:2","basePayAmount":1500,"currentEmploymentIndicator":true,"militaryEntitlement":0},{"id":"loan:application:0:employment:3","currentEmploymentIndicator":true,"militaryEntitlement":0,"overtimeAmount":0},{"id":"loan:application:0:employment:4","bonusAmount":0,"currentEmploymentIndicator":true,"militaryEntitlement":0}],"firstName":"Juanito","firstNameWithMiddleName":"JuanitoAntonio","fullName":"JuanitoAntonioPerez","hmdaGendertypeDoNotWishIndicator":false,"hmdaEthnicityDoNotWishIndicator":true,"hmdaRaceDoNotWishProvideIndicator":true,"homeownerPastThreeYearsIndicator":false,"intentToOccupyIndicator":true,"isBorrower":true,"isEthnicityBasedOnVisual":"N","isRaceBasedOnVisual":"N","isSexBasedOnVisual":"N","lastName":"Perez","lastNameWithSuffix":"Perez","maritalStatusType":"Unmarried","middleName":"Antonio","outstandingJudgementsIndicator":false,"presentlyDelinquentIndicatorUrla":true,"priorPropertyDeedInLieuConveyedIndicator":true,"priorPropertyForeclosureCompletedIndicator":true,"priorPropertyShortSaleCompletedIndicator":false,"propertyProposedCleanEnergyLienIndicator":true,"selfDeclaredMilitaryServiceIndicator":false,"taxIdentificationIdentifier":"990-90-0009","undisclosedBorrowedFundsIndicator":true,"undisclosedCreditApplicationIndicator":true,"undisclosedMortgageApplicationIndicator":false,"urla2020CitizenshipResidencyType":"PermanentResidentAlien"},"coborrower":{"applicantType":"CoBorrower","isBorrower":false},"otherAssets":[{"id":"loan:application:0:otherasset:1","borrowerType":"Borrower","assetType":"EarnestMoney","cashOrMarketValue":1000}],"reoProperties":[{"id":"loan:application:0:reoproperty:1","dispositionStatusType":"RetainForRental","maintenanceExpenseAmount":"10000","marketValueAmount":20000000,"owner":"Borrower","printAttachIndicator":true,"propertyUsageType":"SecondHome","futurePropertyUsageType":"SecondHome"}]}],"borrowerPairCount":1,"borrowerRequestedLoanAmount":100000,"buydownIndicator":false,"closingDocument":{"closingProvider":"EncompassDocs"},"contacts":[{"contactType":"BROKER_LENDER","fhaLenderId":"2299500056"}],"currentApplicationIndex":0,"docEngine":"New_Encompass_Docs_Solution","estimatedPrepaidItemsAmount":2300,"fhaVaLoan":{"sponsorId":"2299500056"},"gfe":{"fundingAmount":0,"lockField":true},"lenderCaseIdentifier":"1122334455","loanAmortizationTermMonths":360,"loanAmortizationType":"Fixed","loanImportStatusIndicator":true,"loanProductData":{"balloonIndicator":false,"gsePropertyType":"Detached","lienPriorityType":"FirstLien","prepaymentPenaltyIndicator":false},"loanSource":"ULAD(MISMO3.4)","mortgageType":"FHA","originationDate":"2020-08-07","principalAndInterestMonthlyPaymentAmount":914,"print2003Application":"2020","property":{"addressLineText":"78112Oregon207","city":"Hermiston","county":"Morrow","fhaSecondaryResidenceIndicator":true,"financedNumberOfUnits":1,"loanPurposeType":"NoCash-OutRefinance","postalCode":"97838","propertyMixedUsageIndicator":false,"propertyRightsType":"FeeSimple","state":"OR"},"propertyEstimatedValueAmount":1000,"purchasePriceAmount":32000000,"rateLock":{"correspondentWarehouseBankId":0,"daysToExtend":0,"rateRequestStatus":"NotLocked-NoRequest","isCancelled":"N","rateStatus":"NotLocked","requestPending":"N","requestType":"Lock","extensionRequestPending":"N","cancellationRequestPending":"N","reLockRequestPending":"N"},"regulationZ":{"interestOnlyIndicator":false},"requestedInterestRatePercent":4.5,"sellerPaidClosingCostsAmount":2250,"tql":{"tqlFraudAlertsTotal":0,"tqlFraudAlertsTotalHigh":0,"tqlFraudAlertsTotalMedium":0,"tqlFraudAlertsTotalLow":0,"tqlFraudAlertsTotalHighUnaddressed":0,"tqlFraudAlertsTotalMediumUnaddressed":0,"tqlFraudAlertsTotalLowUnaddressed":0,"driveScore":0,"driveIdVerifyScore":0,"driveAppVerifyScore":0,"drivePropertyVerifyScore":0},"uldd":{"countryCode":"US","ginnieConstructionMethodType":"SiteBuilt"},"use2018DiIndicator":true,"constructionLoanIndicator":false,"propertyExistingCleanEnergyLienIndicator":false,"productDescription":"30YrFixed","borrEstimatedClosingCostsAmount":6750,"pudIndicator":false,"notInProjectIndicator":true,"loanFeaturesOtherIndicator":true}';
        
        // dd($myArray);
        

        // dd(json_decode(json_encode($convertXml->getDecodedBody()),true));

        // return $this->newImport(json_decode(json_encode($convertXml->getDecodedBody()),true),'Zense_Import');
        return $this->newImport($myArray,'Zense_Import');

    }

    private function array_filter_recursive($input){
        foreach ($input as &$value){
            if (is_array($value)){
                $value = $this->array_filter_recursive($value);
            }
        }
        return array_filter($input);
    }
    
    


}
