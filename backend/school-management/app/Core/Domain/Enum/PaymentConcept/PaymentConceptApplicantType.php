<?php

namespace App\Core\Domain\Enum\PaymentConcept;

/**
 * @OA\Schema(
 *     schema="PaymentConceptApplicantType",
 *     type="string",
 *     description="Tags de casos especiales",
 *     enum={"applicant", "no_student_details"},
 *     example="applicant"
 * )
 */
enum PaymentConceptApplicantType: string
{
    case APPLICANT = 'applicant';
    case NO_STUDENT_DETAILS = 'no_student_details';
}
