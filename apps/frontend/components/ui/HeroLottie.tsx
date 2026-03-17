"use client";

import dynamic from "next/dynamic";
import animationData from "@/components/ui/chat-pulse.json";

const Lottie = dynamic(() => import("lottie-react"), { ssr: false });

export function HeroLottie() {
  return (
    <div className="h-16 w-16" aria-hidden="true">
      <Lottie animationData={animationData} loop />
    </div>
  );
}
