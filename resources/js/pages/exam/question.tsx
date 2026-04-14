import React from 'react';

interface Question {
  position: number;
  question_id: number;
  text: string;
  options: { id: number; text: string }[];
  selected_option_ids: number[];
  flagged: boolean;
}

interface Attempt {
  id: number;
  timer_expires_at: string;
  total_questions: number;
}

interface Props {
  attempt: Attempt;
  questions: Question[];
}

export default function Question({ attempt, questions }: Props) {
  return (
    <div className="exam-question-page">
      {/* Placeholder component - to be implemented in Task 12 */}
    </div>
  );
}
