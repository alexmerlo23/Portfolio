import { useEffect, useState } from "react";

export const StarBackground = ({ onClick }) => {
  const [stars, setStars] = useState([]);
  const [meteors, setMeteors] = useState([]);
  const [clickMeteors, setClickMeteors] = useState([]);

  useEffect(() => {
    generateStars();
    generateMeteors();

    const handleResize = () => {
      generateStars();
    };

    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  const generateStars = () => {
    const numberOfStars = Math.floor(
      (window.innerWidth * window.innerHeight) / 10000
    );

    const newStars = [];
    for (let i = 0; i < numberOfStars; i++) {
      newStars.push({
        id: i,
        size: Math.random() * 3 + 1,
        x: Math.random() * 100,
        y: Math.random() * 100,
        opacity: Math.random() * 0.5 + 0.5,
        animationDuration: Math.random() * 4 + 2,
      });
    }
    setStars(newStars);
  };

  const generateMeteors = () => {
    const numberOfMeteors = 6;
    const newMeteors = [];

    for (let i = 0; i < numberOfMeteors; i++) {
      newMeteors.push({
        id: i,
        size: Math.random() * 2 + 1,
        x: Math.random() * 100,
        y: Math.random() * 50,
        delay: Math.random() * 25,
        animationDuration: Math.random() * 3 + 3,
      });
    }

    setMeteors(newMeteors);
  };

  const addClickMeteor = (x, y) => {
    const size = Math.random() * 2 + 1;
    const id = Date.now();

    const newClickMeteor = {
      id,
      size,
      x, // Percentage-based x
      y, // Percentage-based y
    };

    setClickMeteors((prev) => [...prev, newClickMeteor]);

    setTimeout(() => {
      setClickMeteors((prev) => prev.filter((m) => m.id !== id));
    }, 1500);
  };

  // Expose addClickMeteor to parent component
  useEffect(() => {
    if (onClick) {
      const handleClick = (e) => {
        const { clientX, clientY } = e;
        const xPercent = (clientX / window.innerWidth) * 100;
        const yPercent = (clientY / window.innerHeight) * 100;
        addClickMeteor(xPercent, yPercent);
      };
      window.addEventListener("click", handleClick);
      return () => window.removeEventListener("click", handleClick);
    }
  }, [onClick]);

  return (
    <div className="fixed inset-0 overflow-hidden z-0">
      {stars.map((star) => (
        <div
          key={star.id}
          className="star animate-pulse-subtle"
          style={{
            width: `${star.size}px`,
            height: `${star.size}px`,
            left: `${star.x}%`,
            top: `${star.y}%`,
            opacity: star.opacity,
            animationDuration: `${star.animationDuration}s`,
          }}
        />
      ))}

      {meteors.map((meteor) => (
        <div
          key={meteor.id}
          className="meteor animate-meteor"
          style={{
            width: `${meteor.size * 40}px`,
            height: `${meteor.size * 2}px`,
            left: `${meteor.x}%`,
            top: `${meteor.y}%`,
            
            animationDuration: `${meteor.animationDuration}s`,
          }}
        />
      ))}

      {clickMeteors.map((meteor) => (
        <div
          key={meteor.id}
          className="click-meteor animate-click-meteor"
          style={{
            width: `${meteor.size * 50}px`,
            height: `${meteor.size * 2}px`,
            left: `${meteor.x}%`,
            top: `${meteor.y}%`,
          }}
        />
      ))}
    </div>
  );
};