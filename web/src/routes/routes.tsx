import React, { lazy } from 'react';
import { useRoutes, Navigate, Outlet, useLocation } from 'react-router-dom';

import MainLayout from '@/components/Layouts/MainLayout';
import { isLogged } from '@/utils/auth';

const Home = lazy(() => import('@/pages/home/index'));
const About = lazy(() => import('@/pages/home/about'));

const ProblemIndex = lazy(() => import('@/pages/problem/index'))
const ProblemCreate = lazy(() => import('@/pages/problem/create'))
const ProblemUpdate = lazy(() => import('@/pages/problem/update'))
const ProblemDetail = lazy(() => import('@/pages/problem/detail'))

const UserLogin = lazy(() => import('@/pages/user/login'))
const UserRegister = lazy(() => import('@/pages/user/register'))
const UserView = lazy(() => import('@/pages/user/view'))
const UserSetting = lazy(() => import('@/pages/user/setting'))

const ContestIndex = lazy(() => import('@/pages/contest/list'))
const ContestDetail = lazy(() => import('@/pages/contest/detail'))

const ProtectedRoute = () => {
  const location = useLocation();
  if (!isLogged()) {
    // user is not authenticated
    return <Navigate to="/login" state={{ from: location }}  />;
  }
  return <Outlet />;
};

const routeList = [
  {
    path: "/",
    element: <MainLayout />,
    children: [
      { index: true, element: <Home /> },
      { path: '/about', element: <About /> },
      { path: '/problems', element: <ProblemIndex /> },
      { path: '/problems/:id', element: <ProblemDetail /> },
      {
        path: '/problem',
        element: <ProtectedRoute />,
        children: [
          { path: '/problem/create', element: <ProblemCreate /> },
          { path: '/problem/update/:id', element: <ProblemUpdate /> },
        ]
      },
      { path: '/contests', element: <ContestIndex /> },
      { path: '/contests/:id/*', element: <ContestDetail /> },

      { path: '/login', element: <UserLogin /> },
      { path: '/register', element: <UserRegister /> },
      { path: '/users/:id', element: <UserView /> },
      {
        path: '/user',
        element: <ProtectedRoute />,
        children: [
          { path: '/user/setting', element: <UserSetting /> },
        ]
      }
    ],
  },
];

const RenderRouter = () => {
  return useRoutes(routeList);
};

export default RenderRouter;
