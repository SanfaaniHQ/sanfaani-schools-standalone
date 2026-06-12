# School CBT Operations

CBT remains the assessment engine for Sanfaani Schools Standalone.

The existing CBT area continues to own:

- question banks;
- exams, quizzes, and assessments;
- exam questions and marks;
- access codes and candidate entry;
- attempts, autosave, submission, grading, and result release;
- CBT result publication into the result system.

## LMS Links

Stage 15 lets authorized school users link existing CBT items to LMS classrooms or LMS materials. LMS links do not create exams, copy questions, start attempts, publish results, or expose private CBT payloads.

Teachers can link or unlink CBT items only for assigned LMS class/subject scopes and matching CBT class/subject scopes. School Admins can link school-scoped CBT items that match the LMS scope when a CBT scope is present.

## Student Boundary

Students must still enter CBT through the existing CBT access flow. LMS does not bypass:

- exam open/scheduled/published checks;
- candidate or admission-number rules;
- generated access-code rules;
- attempt limits;
- result-release rules.

## Deferred

Live classes are scheduled in the separate manual-link foundation and do not change CBT behavior. Provider abstraction, live-class provider APIs, offline LMS, assignment submissions/grading, discussion forums, video hosting, analytics, parent LMS portal, and payment-gated content are outside the CBT integration stage.
