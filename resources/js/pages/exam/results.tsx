import React from 'react';

interface Attempt {
  id: number;
  score: number;
  total_questions: number;
  passed: boolean;
  submitted_at: string;
  is_claimed: boolean;
}

interface TopicBreakdownItem {
  topic: string;
  correct: number;
  total: number;
  percentage: number;
}

interface Props {
  attempt: Attempt;
  topicBreakdown: TopicBreakdownItem[];
}

export default function Results({ attempt, topicBreakdown }: Props) {
  return (
    <div className="exam-results-page">
      {/* Placeholder component - to be implemented in Task 13 */}
    </div>
  );
}
