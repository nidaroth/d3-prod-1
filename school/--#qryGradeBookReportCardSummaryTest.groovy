--#qryGradeBookReportCardSummaryTest
SELECT
    GradeBookStudent.StudentNo,
    StudentSelection.SortOrder,
    GradeBookCode.Code,
    GradeBookCode.Description,
    GradeBookType.GradeBookType,
    Sum(GradeBookStudent.CompletedHours) AS CompletedHours,
    Sum(GradeBookStudent.CompletedSessions) AS CompletedSessions,
    Avg(GradeBookStudent.CompletedPoints) AS CompletedPoints,
    GradeBookStudentRequirements.RequiredHours,
    GradeBookStudentRequirements.RequiredSessions,
    GradeBookStudentRequirements.RequiredPoints
FROM
    GradeBookStudent
LEFT JOIN GradeBookCode ON GradeBookStudent.GradeBookCodeID = GradeBookCode.GradeBookCodeID
LEFT JOIN GradeBookStudentRequirements ON
    GradeBookStudent.GradeBookCodeID = GradeBookStudentRequirements.GradeBookCodeID
    AND GradeBookStudent.StudentNo = GradeBookStudentRequirements.StudentNo
LEFT JOIN GradeBookType ON GradeBookCode.TypeID = GradeBookType.GradeBookTypeID
INNER JOIN StudentSelection ON GradeBookStudent.StudentNo = StudentSelection.StudentNo
WHERE
    GradeBookType.GradeBookReportType = 'test'
    AND GradeBookStudent.CompletedDate <= #DDDate#
GROUP BY
    GradeBookStudent.StudentNo,
    StudentSelection.SortOrder,
    GradeBookCode.Code,
    GradeBookCode.Description,
    GradeBookType.GradeBookType,
    GradeBookStudentRequirements.RequiredHours,
    GradeBookStudentRequirements.RequiredSessions,
    GradeBookStudentRequirements.RequiredPoints,
    GradeBookStudent.ID;


SELECT
    GradeBookStudent.StudentNo,
    StudentSelection.SortOrder,
    GradeBookCode.Code,
    GradeBookCode.Description,
    GradeBookType.GradeBookType,
    Sum(GradeBookStudent.CompletedHours) AS CompletedHours,
    Sum(GradeBookStudent.CompletedSessions) AS CompletedSessions,
    Avg(GradeBookStudent.CompletedPoints) AS CompletedPoints,
    GradeBookStudentRequirements.RequiredHours,
    GradeBookStudentRequirements.RequiredSessions,
    GradeBookStudentRequirements.RequiredPoints
FROM
    GradeBookStudent
LEFT JOIN GradeBookCode ON GradeBookStudent.GradeBookCodeID = GradeBookCode.GradeBookCodeID
LEFT JOIN GradeBookStudentRequirements ON
    GradeBookStudent.GradeBookCodeID = GradeBookStudentRequirements.GradeBookCodeID
    AND GradeBookStudent.StudentNo = GradeBookStudentRequirements.StudentNo
LEFT JOIN GradeBookType ON GradeBookCode.TypeID = GradeBookType.GradeBookTypeID
INNER JOIN StudentSelection ON GradeBookStudent.StudentNo = StudentSelection.StudentNo
WHERE
    GradeBookType.GradeBookReportType = 'test'
    AND GradeBookStudent.CompletedDate <= #DDDate#
GROUP BY
    GradeBookStudent.StudentNo,
    StudentSelection.SortOrder,
    GradeBookCode.Code,
    GradeBookCode.Description,
    GradeBookType.GradeBookType,
    GradeBookStudentRequirements.RequiredHours,
    GradeBookStudentRequirements.RequiredSessions,
    GradeBookStudentRequirements.RequiredPoints,
    GradeBookStudent.ID;



--#qryGradeBookReportCardSummaryLab
SELECT
    GBS.StudentNo,
    StudentSelection.SortOrder,
    GBC.Code,
    GBC.Description,
    GBT.GradeBookType,
    Sum(GBS.CompletedHours) AS CompletedHours,
    Sum(GBS.CompletedSessions) AS CompletedSessions,
    Avg(GBS.CompletedPoints) AS CompletedPoints,
    GBSR.RequiredHours,
    GBSR.RequiredSessions,
    GBSR.RequiredPoints
FROM
    GradeBookStudent AS GBS
INNER JOIN StudentSelection ON GBS.StudentNo = StudentSelection.StudentNo
INNER JOIN GradeBookCode AS GBC ON GBS.GradeBookCodeID = GBC.GradeBookCodeID
INNER JOIN GradeBookType AS GBT ON GBC.TypeID = GBT.GradeBookTypeID
LEFT JOIN GradeBookStudentRequirements AS GBSR ON GBS.GradeBookCodeID = GBSR.GradeBookCodeID
    AND GBS.StudentNo = GBSR.StudentNo
WHERE
    GBT.GradeBookReportType = 'Lab'
    AND GBS.CompletedDate <= #DDDate#
GROUP BY
    GBS.StudentNo,
    StudentSelection.SortOrder,
    GBC.Code,
    GBC.Description,
    GBT.GradeBookType,
    GBSR.RequiredHours,
    GBSR.RequiredSessions,
    GBSR.RequiredPoints;
