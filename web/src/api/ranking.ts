import axios from '@/utils/request';

// 解答排行榜
export function listProblemRankings(params=undefined) {
  return axios.get(`/problem_rankings`, {params});
}
